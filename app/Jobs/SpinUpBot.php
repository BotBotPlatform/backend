<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use App\Models\Bot;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SpinUpBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Check that user has a bot
        $bot = $this->user->bot;
        if(count($bot) <= 0) {
          throw new ProcessFailedException("User ".$this->user->email."does not have a bot");
        }

        //Check if process is running
        $downloadCommand = "pm2 list";
        $process = new Process($downloadCommand);
        $process->run();
        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        } else {
          $this->info("Output:".$process->getOutput());
          $this->info("Success");
        }
    }
}
