<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MailConfigurationRequest;
use App\Services\MailConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class MailConfigurationController extends Controller
{
    public function __construct(
        protected MailConfigurationService $mailConfigService
    ) {}

    /**
     * Show mail configuration page
     */
    public function index(): Response
    {
        $mailConfig = $this->mailConfigService->getMailConfiguration();

        return Inertia::render('Dashboard/Settings/GeneralSettings', [
            'mailConfig' => $mailConfig,
        ]);
    }

    /**
     * Save mail configuration
     */
    public function store(MailConfigurationRequest $request)
    {
        try {
            $this->mailConfigService->saveMailConfiguration($request->validated());
            
            $mailConfig = $this->mailConfigService->getMailConfiguration();

            return redirect()->route('settings.general')->with([
                'mailConfig' => $mailConfig,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Failed to save mail configuration: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Test mail connection
     */
    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_mailer' => ['required', 'string'],
            'mail_host' => ['required', 'string'],
            'mail_port' => ['required', 'string'],
            'mail_username' => ['required', 'string'],
            'mail_password' => ['required', 'string'],
            'mail_encryption' => ['required', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['required', 'email'],
        ], [
            'mail_mailer.required' => 'Please enter mail mailer',
            'mail_host.required' => 'Please enter mail host',
            'mail_port.required' => 'Please enter mail port',
            'mail_username.required' => 'Please enter mail username',
            'mail_password.required' => 'Please enter mail password',
            'mail_encryption.required' => 'Please enter mail encryption',
            'mail_from_address.required' => 'Please enter mail from address',
            'mail_from_address.email' => 'Please enter a valid email address',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        try {
            $config = $validator->validated();

            // Temporarily update mail configuration
            $this->updateTemporaryMailConfig($config);

            // Send test email
            $testEmail = $config['mail_from_address'];
            
            Mail::raw('This is a test email from EventHub. Your mail configuration is working correctly!', function ($message) use ($testEmail) {
                $message->to($testEmail)
                    ->subject('Test Connection - EventHub Mail Configuration');
            });

            return back()->with('success', 'Test connection successful! A test email has been sent to ' . $testEmail);
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Test connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Temporarily update mail configuration for testing
     */
    protected function updateTemporaryMailConfig(array $config): void
    {
        Config::set('mail.default', $config['mail_mailer']);
        
        if ($config['mail_mailer'] === 'smtp') {
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $config['mail_host'],
                'port' => $config['mail_port'],
                'encryption' => $config['mail_encryption'] !== 'none' ? $config['mail_encryption'] : null,
                'username' => $config['mail_username'],
                'password' => $config['mail_password'],
                'timeout' => null,
            ]);
        }

        Config::set('mail.from.address', $config['mail_from_address']);
        Config::set('mail.from.name', config('app.name'));
    }
}

