<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SsoSetting;
use Livewire\WithPagination;

class SsoManagement extends Component
{
    use WithPagination;

    public $type = 'email'; // 'email' or 'domain'
    public $value = '';
    public $description = '';
    public $editingId = null;
    public $showAddForm = false;

    protected $rules = [
        'type' => 'required|in:email,domain',
        'value' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'value.required' => 'Please enter an email address or domain.',
        'type.required' => 'Please select a type.',
    ];

    public function mount()
    {
        // Initialize component
    }

    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->type = 'email';
        $this->value = '';
        $this->description = '';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // Additional validation based on type
        if ($this->type === 'email' && !filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->addError('value', 'Please enter a valid email address.');
            return;
        }

        if ($this->type === 'domain' && !preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $this->value)) {
            $this->addError('value', 'Please enter a valid domain (e.g., example.com).');
            return;
        }

        try {
            if ($this->editingId) {
                // Update existing setting
                $setting = SsoSetting::findOrFail($this->editingId);
                $setting->update([
                    'type' => $this->type,
                    'value' => $this->value,
                    'description' => $this->description,
                ]);
                session()->flash('message', 'SSO setting updated successfully!');
            } else {
                // Create new setting
                SsoSetting::create([
                    'type' => $this->type,
                    'value' => $this->value,
                    'description' => $this->description,
                ]);
                session()->flash('message', 'SSO setting added successfully!');
            }

            $this->resetForm();
            $this->showAddForm = false;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique')) {
                $this->addError('value', 'This ' . $this->type . ' is already configured.');
            } else {
                session()->flash('error', 'An error occurred while saving the setting.');
            }
        }
    }

    public function edit($id)
    {
        $setting = SsoSetting::findOrFail($id);
        $this->editingId = $id;
        $this->type = $setting->type;
        $this->value = $setting->value;
        $this->description = $setting->description;
        $this->showAddForm = true;
    }

    public function delete($id)
    {
        try {
            SsoSetting::findOrFail($id)->delete();
            session()->flash('message', 'SSO setting deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the setting.');
        }
    }

    public function toggleStatus($id)
    {
        try {
            $setting = SsoSetting::findOrFail($id);
            $setting->update(['is_active' => !$setting->is_active]);
            session()->flash('message', 'SSO setting status updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while updating the setting status.');
        }
    }

    public function render()
    {
        $settings = SsoSetting::orderBy('type')->orderBy('value')->paginate(10);

        return view('livewire.sso-management', [
            'settings' => $settings
        ]);
    }
}
