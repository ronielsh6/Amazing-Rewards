<?php

namespace App\Jobs;

use App\Mail\BuildMail;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\User;
use App\Services\CloudMessages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private CloudMessages $cloudMessages;

    private User $user;

    private Campaign $campaign;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        CloudMessages $cloudMessages,
        User $user,
        Campaign $campaign
    ) {
        $this->campaign = $campaign;
        $this->user = $user;
        $this->cloudMessages = $cloudMessages;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->campaign->is_push){
            $this->cloudMessages->sendMessage($this->campaign->title, $this->campaign->body, $this->user, ['deep_link' => $this->campaign->deep_link], true);
        }
        if ($this->campaign->is_email){
            Mail::to($this->user->email)->send(new CampaignMail($this->campaign));
        }
    }
}
