<?php

namespace App\Livewire\WebsiteMonitoring;

use Livewire\Component;
use App\Models\Website;
use App\Models\WebsiteUrl;
use Illuminate\Support\Facades\Auth;

class AddWebsite extends Component
{
    public $name = '';
    public $description = '';
    public $notification_emails = [''];
    public $urls = [
        [
            'url' => '',
            'monitor_status' => true,
            'monitor_domain' => false,
            'monitor_ssl' => false,
        ]
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'notification_emails.*' => 'nullable|email',
        'urls.*.url' => 'required|url',
        'urls.*.monitor_status' => 'boolean',
        'urls.*.monitor_domain' => 'boolean',
        'urls.*.monitor_ssl' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Website name is required.',
        'notification_emails.*.email' => 'Please enter a valid email address.',
        'urls.*.url.required' => 'URL is required.',
        'urls.*.url.url' => 'Please enter a valid URL.',
    ];

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function addUrl()
    {
        $this->urls[] = [
            'url' => '',
            'monitor_status' => true,
            'monitor_domain' => false,
            'monitor_ssl' => false,
        ];
    }

    public function removeUrl($index)
    {
        if (count($this->urls) > 1) {
            unset($this->urls[$index]);
            $this->urls = array_values($this->urls);
        }
    }

    public function addEmail()
    {
        $this->notification_emails[] = '';
    }

    public function removeEmail($index)
    {
        if (count($this->notification_emails) > 1) {
            unset($this->notification_emails[$index]);
            $this->notification_emails = array_values($this->notification_emails);
        }
    }

    public function save()
    {
        $this->validate();

        try {
            // Filter out empty emails
            $emails = array_filter($this->notification_emails, function($email) {
                return !empty(trim($email));
            });

            // Create the website
            $website = Website::create([
                'name' => $this->name,
                'description' => $this->description,
                'notification_emails' => array_values($emails),
                'is_active' => true,
                'overall_status' => 'unknown',
            ]);

            // Create the URLs
            foreach ($this->urls as $urlData) {
                WebsiteUrl::create([
                    'website_id' => $website->id,
                    'url' => $urlData['url'],
                    'monitor_status' => $urlData['monitor_status'],
                    'monitor_domain' => $urlData['monitor_domain'],
                    'monitor_ssl' => $urlData['monitor_ssl'],
                    'current_status' => 'unknown',
                ]);
            }

            session()->flash('message', 'Website added successfully!');
            
            return redirect()->route('website-monitoring.list');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add website: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('website-monitoring.list');
    }

    public function render()
    {
        return view('livewire.website-monitoring.add-website')
            ->layout('layouts.main');
    }
}
