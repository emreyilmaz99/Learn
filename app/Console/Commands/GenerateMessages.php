<?php

namespace App\Console\Commands;

use App\Jobs\IndexMessageJob;
use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:generate {--count=10000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random messages between existing users and dispatch index jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $this->info("Generating {$count} random messages...");

        $userIds = User::pluck('id')->toArray();
        if (count($userIds) < 2) {
            $this->error('Not enough users to generate messages. Need at least 2 users.');
            return 1;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            // Rastgele sender ve receiver seç (farklı olsun)
            do {
                $senderId = $userIds[array_rand($userIds)];
                $receiverId = $userIds[array_rand($userIds)];
            } while ($senderId === $receiverId);

            // Rastgele title ve content oluştur
            $title = 'Message ' . ($i + 1) . ': ' . fake()->sentence(5);
            $content = fake()->paragraphs(2, true);

            // Mesajı oluştur
            $message = Message::create([
                'title' => $title,
                'content' => $content,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
            ]);

            // Index job'u dispatch et
            dispatch(new IndexMessageJob($message->id));

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated and dispatched index jobs for {$count} messages.");
        $this->info('Run queue workers to process indexing: php artisan queue:work');

        return 0;
    }
}
