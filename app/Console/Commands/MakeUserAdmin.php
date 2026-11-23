<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'admin:make {email : The registered user email}';

    protected $description = 'Grant administrator access to a registered user';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error("No user was found with the email '{$email}'.");
            $this->line('Registered emails in the current database:');
            User::query()->orderBy('id')->pluck('email')->each(fn (string $registeredEmail) => $this->line('- '.$registeredEmail));
            $this->newLine();
            $this->comment('Make sure you are using the same .env database and that this account has registered already.');
            return self::FAILURE;
        }

        $user->update(['role' => 'admin']);
        $this->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}
