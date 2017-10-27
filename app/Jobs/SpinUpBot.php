<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use App\Models\Bot;
use App\Http\Controllers\BotController;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

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
          throw new Exception("User ".$this->user->email."does not have a bot");
        }

        //Check if process is running
        $newPort = BotController::getFreePort();
        $jwtToken = $this->user->getToken();
        $downloadCommand = "pm2 start ".getenv('NODE_PATH')." --name=".$bot->uuid." -- ".$newPort." ".$jwtToken;
        $process = new Process($downloadCommand);
        $process->run();
        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        } else {
          //Success
          $bot->deploy_status = "alive";
          $bot->port = $newPort;
          $bot->save();
        }
    }

    public function failed(Exception $exception)
   {
       // Bot deploy failed

       //Does the userh have a bot?
       $bot = $this->user->bot;
       if(count($bot) <= 0) {
          //The user just doesn't have a bot, nothing to report here
          return;
       }
       //TODO- Make the node process update a heartbeat value
       $bot->deploy_status = "failed";
       $bot->save();
   }
}
