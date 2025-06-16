<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SSOController extends Controller
{
    /**
     * Check if email is allowed for SSO
     */
    private function isEmailAllowedForSSO(string $email): bool
    {
        $allowedEmails = config('sso.allowed_emails', []);
        $allowedDomains = config('sso.allowed_domains', []);

        // Check exact email matches
        if (in_array($email, $allowedEmails)) {
            return true;
        }

        // Check domain matches
        $domain = substr(strrchr($email, "@"), 1);
        return in_array($domain, $allowedDomains);
    }

    /**
     * Redirect to Azure AD for authentication
     */
    public function redirectToAzure()
    {
        try {
            $config = config('services.azure');
            $scopes = config('sso.provider.scopes', ['openid', 'profile', 'email']);

            $provider = new \SocialiteProviders\Azure\Provider(
                request(),
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );

            return $provider->scopes($scopes)->redirect();
        } catch (\Exception $e) {
            Log::error('Azure SSO redirect error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'SSO service is currently unavailable.');
        }
    }

    /**
     * Handle Azure AD callback
     */
    public function handleAzureCallback(Request $request)
    {
        try {
            $config = config('services.azure');

            $provider = new \SocialiteProviders\Azure\Provider(
                request(),
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );

            $azureUser = $provider->user();

            // Check if email is allowed for SSO
            if (!$this->isEmailAllowedForSSO($azureUser->getEmail())) {
                Log::warning('Unauthorized SSO attempt from: ' . $azureUser->getEmail());
                return redirect('/login')->with('error', 'Your email is not authorized for SSO access.');
            }

            // Find or create user
            $user = $this->findOrCreateUser($azureUser);

            // Update last SSO login
            $user->update(['last_sso_login' => now()]);

            // Log the user in
            Auth::login($user, true);

            Log::info('Successful SSO login for: ' . $user->email);

            return redirect()->intended('/main-dashboard');

        } catch (\Exception $e) {
            Log::error('Azure SSO callback error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Find or create user from Azure AD data
     */
    private function findOrCreateUser($azureUser)
    {
        // First, try to find user by Azure ID
        $user = User::where('azure_id', $azureUser->getId())->first();

        if ($user) {
            // Update user info from Azure
            $user->update([
                'name' => $azureUser->getName(),
                'email' => $azureUser->getEmail(),
                'avatar' => $azureUser->getAvatar(),
                'is_sso_enabled' => true,
            ]);
            return $user;
        }

        // Try to find user by email
        $user = User::where('email', $azureUser->getEmail())->first();

        if ($user) {
            // Link existing user to Azure
            $user->update([
                'azure_id' => $azureUser->getId(),
                'name' => $azureUser->getName(),
                'avatar' => $azureUser->getAvatar(),
                'is_sso_enabled' => true,
            ]);
            return $user;
        }

        // Create new user (if auto-creation is enabled)
        if (!config('sso.auto_create_users', true)) {
            throw new \Exception('User not found and auto-creation is disabled');
        }

        return User::create([
            'name' => $azureUser->getName(),
            'email' => $azureUser->getEmail(),
            'azure_id' => $azureUser->getId(),
            'avatar' => $azureUser->getAvatar(),
            'is_sso_enabled' => true,
            'password' => bcrypt(str()->random(32)), // Random password for SSO users
        ]);
    }

    /**
     * Check if user can use SSO
     */
    public function canUseSSO(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json(['can_use_sso' => false]);
        }

        return response()->json([
            'can_use_sso' => $this->isEmailAllowedForSSO($email)
        ]);
    }
}
