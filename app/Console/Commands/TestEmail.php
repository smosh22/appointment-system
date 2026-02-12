<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {recipient?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('recipient') ?? config('mail.from.address');

        $this->info('Testing email configuration...');
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('SMTP Username: ' . config('mail.mailers.smtp.username'));
        $this->info('Encryption: ' . config('mail.mailers.smtp.encryption'));
        $this->info('From Address: ' . config('mail.from.address'));
        $this->info('From Name: ' . config('mail.from.name'));
        $this->newLine();

        try {
            // Enable Swift Mailer debug mode
            $this->info('Attempting to send email...');
            
            Mail::raw('This is a test email from your Laravel application using Brevo SMTP.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Test Email - Brevo SMTP Configuration')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->info("✓ Test email sent successfully to: {$recipient}");
            $this->info('Please check your inbox (and spam folder).');
            $this->newLine();
            $this->warn('IMPORTANT: Check your Brevo dashboard to verify:');
            $this->warn('1. The sender email "' . config('mail.from.address') . '" is verified');
            $this->warn('2. The sender name "' . config('mail.from.name') . '" matches your Brevo sender');
            $this->warn('3. You have not exceeded your daily sending limit');
            $this->warn('4. Check Brevo logs for any rejected emails');
            
            return Command::SUCCESS;
        } catch (\Swift_TransportException $e) {
            $this->error('✗ SMTP Transport Error!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('This usually means:');
            $this->warn('- SMTP credentials are incorrect');
            $this->warn('- Port 587 is blocked by firewall');
            $this->warn('- Brevo API key is invalid or expired');
            
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('✗ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Error Type: ' . get_class($e));
            $this->newLine();
            $this->warn('Common issues:');
            $this->warn('1. Check if your Brevo API key is correct');
            $this->warn('2. Verify your sender email is verified in Brevo');
            $this->warn('3. Check if port 587 is not blocked by firewall');
            $this->warn('4. Ensure TLS encryption is supported');
            $this->warn('5. Verify sender name matches Brevo configuration');
            
            return Command::FAILURE;
        }
    }
}
