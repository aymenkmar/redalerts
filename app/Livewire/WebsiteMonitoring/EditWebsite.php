<?php

namespace App\Livewire\WebsiteMonitoring;

use App\Models\Website;
use App\Models\WebsiteUrl;
use Livewire\Component;

class EditWebsite extends Component
{
    public Website $website;
    public $name;
    public $description;
    public $notification_emails = [];
    public $urls = [];
    public $is_active;

    public function mount(Website $website)
    {
        $this->website = $website;
        $this->name = $website->name;
        $this->description = $website->description;
        $this->notification_emails = $website->notification_emails ?? [];
        $this->is_active = $website->is_active;

        // Ensure we have at least one email
        if (empty($this->notification_emails)) {
            $this->notification_emails = [''];
        }

        // Load existing URLs
        $this->urls = $website->urls->map(function ($url) {
            return [
                'id' => $url->id,
                'url' => $url->url,
                'monitor_status' => $url->monitor_status,
                'monitor_domain' => $url->monitor_domain,
                'monitor_ssl' => $url->monitor_ssl,
            ];
        })->toArray();

        // Ensure we have at least one URL
        if (empty($this->urls)) {
            $this->urls = [[
                'id' => null,
                'url' => '',
                'monitor_status' => true,
                'monitor_domain' => false,
                'monitor_ssl' => false,
            ]];
        }
    }

    public function addEmail()
    {
        $this->notification_emails[] = '';
    }

    public function removeEmail($index)
    {
        unset($this->notification_emails[$index]);
        $this->notification_emails = array_values($this->notification_emails);
    }

    public function addUrl()
    {
        $this->urls[] = [
            'id' => null,
            'url' => '',
            'monitor_status' => true,
            'monitor_domain' => false,
            'monitor_ssl' => false,
        ];
    }

    public function removeUrl($index)
    {
        // If URL has an ID, we need to delete it from database
        if (isset($this->urls[$index]['id']) && $this->urls[$index]['id']) {
            WebsiteUrl::find($this->urls[$index]['id'])?->delete();
        }

        unset($this->urls[$index]);
        $this->urls = array_values($this->urls);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:websites,name,' . $this->website->id,
            'description' => 'nullable|string|max:500',
            'notification_emails' => 'required|array|min:1',
            'notification_emails.*' => 'required|email',
            'urls' => 'required|array|min:1',
            'urls.*.url' => 'required|url',
        ], [
            'name.unique' => 'This website name is already taken. Please choose a different name.',
        ]);

        // Update website
        $this->website->update([
            'name' => $this->name,
            'description' => $this->description,
            'notification_emails' => array_filter($this->notification_emails),
            'is_active' => $this->is_active,
        ]);

        // Handle URLs
        $existingUrlIds = collect($this->urls)->pluck('id')->filter()->toArray();

        // Delete URLs that are no longer in the list
        $this->website->urls()->whereNotIn('id', $existingUrlIds)->delete();

        // Update or create URLs
        foreach ($this->urls as $urlData) {
            if ($urlData['id']) {
                // Update existing URL
                WebsiteUrl::where('id', $urlData['id'])->update([
                    'url' => $urlData['url'],
                    'monitor_status' => $urlData['monitor_status'],
                    'monitor_domain' => $urlData['monitor_domain'],
                    'monitor_ssl' => $urlData['monitor_ssl'],
                ]);
            } else {
                // Create new URL
                $this->website->urls()->create([
                    'url' => $urlData['url'],
                    'monitor_status' => $urlData['monitor_status'],
                    'monitor_domain' => $urlData['monitor_domain'],
                    'monitor_ssl' => $urlData['monitor_ssl'],
                    'current_status' => 'unknown',
                ]);
            }
        }

        session()->flash('message', 'Website updated successfully!');
        return $this->redirect(route('website-monitoring.list'), navigate: true);
    }

    public function render()
    {
        return view('livewire.website-monitoring.edit-website');
    }
}
