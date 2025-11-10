<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MailConfigurationService
{
    /**
     * Get the path to the .env file
     */
    protected function getEnvPath(): string
    {
        return base_path('.env');
    }

    /**
     * Get current mail configuration from .env
     */
    public function getMailConfiguration(): array
    {
        $envPath = $this->getEnvPath();
        
        if (!File::exists($envPath)) {
            return $this->getDefaultConfiguration();
        }

        $envContent = File::get($envPath);
        
        return [
            'mail_mailer' => $this->getEnvValue($envContent, 'MAIL_MAILER', 'smtp'),
            'mail_host' => $this->getEnvValue($envContent, 'MAIL_HOST', ''),
            'mail_port' => $this->getEnvValue($envContent, 'MAIL_PORT', '2525'),
            'mail_username' => $this->getEnvValue($envContent, 'MAIL_USERNAME', ''),
            'mail_password' => $this->getEnvValue($envContent, 'MAIL_PASSWORD', ''),
            'mail_encryption' => $this->getEnvValue($envContent, 'MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => $this->getEnvValue($envContent, 'MAIL_FROM_ADDRESS', ''),
        ];
    }

    /**
     * Save mail configuration to .env file
     */
    public function saveMailConfiguration(array $config): bool
    {
        $envPath = $this->getEnvPath();
        
        if (!File::exists($envPath)) {
            throw new \RuntimeException('.env file not found');
        }

        $envContent = File::get($envPath);
        
        $envContent = $this->updateEnvValue($envContent, 'MAIL_MAILER', $config['mail_mailer'] ?? 'smtp');
        $envContent = $this->updateEnvValue($envContent, 'MAIL_HOST', $config['mail_host'] ?? '');
        $envContent = $this->updateEnvValue($envContent, 'MAIL_PORT', $config['mail_port'] ?? '2525');
        $envContent = $this->updateEnvValue($envContent, 'MAIL_USERNAME', $config['mail_username'] ?? '');
        $envContent = $this->updateEnvValue($envContent, 'MAIL_PASSWORD', $config['mail_password'] ?? '');
        $envContent = $this->updateEnvValue($envContent, 'MAIL_ENCRYPTION', $config['mail_encryption'] ?? 'tls');
        $envContent = $this->updateEnvValue($envContent, 'MAIL_FROM_ADDRESS', $config['mail_from_address'] ?? '');

        File::put($envPath, $envContent);

        // Clear config cache to reload new values
        if (function_exists('artisan')) {
            \Artisan::call('config:clear');
        }

        return true;
    }

    /**
     * Get default mail configuration
     */
    protected function getDefaultConfiguration(): array
    {
        return [
            'mail_mailer' => 'smtp',
            'mail_host' => '',
            'mail_port' => '2525',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => '',
        ];
    }

    /**
     * Get environment variable value from content
     */
    protected function getEnvValue(string $content, string $key, string $default = ''): string
    {
        $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';
        
        if (preg_match($pattern, $content, $matches)) {
            $value = trim($matches[1]);
            // Remove quotes if present
            $value = trim($value, '"\'');
            return $value ?: $default;
        }

        return $default;
    }

    /**
     * Update environment variable value in content
     */
    protected function updateEnvValue(string $content, string $key, string $value): string
    {
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        $replacement = $key . '=' . $this->formatEnvValue($value);
        
        if (preg_match($pattern, $content)) {
            // Update existing value
            return preg_replace($pattern, $replacement, $content);
        } else {
            // Add new value at the end
            return $content . "\n" . $replacement;
        }
    }

    /**
     * Format value for .env file
     */
    protected function formatEnvValue(string $value): string
    {
        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"' . addslashes($value) . '"';
        }

        return $value;
    }
}

