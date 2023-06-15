<?php

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\Execution;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CampaignJobs
{
    public function __invoke()
    {
        $campaigns = Campaign::all();
        foreach ($campaigns as $campaign) {
            $dateTime = new \DateTime('now');
            $time = date('h:i');
            $startDate = new \DateTime($campaign->start_date);
            $endDate = new \DateTime($campaign->end_date);
            $executionTime = new \DateTime($campaign->execution_time);
            if (($startDate <= $dateTime) && ($endDate >= $dateTime) && $time === $executionTime->format('h:i')) {
                $startTime = date('h:i:s');
                $query = DB::table('users')->leftJoin('gift_card', 'users.id', '=', 'gift_card.owner');
                $query->whereRaw($campaign->parameters);
                $models = $query->get();
                $errors = false;
                foreach ($models as $model) {
                    if ($campaign->is_push) {
                        $result = (new CloudMessages())->sendMessage($campaign->title, $campaign->body, $model, ['deep_link' => $campaign->deep_link, 'image' => $campaign->image, 'image_link' => $campaign->image_link], true);
                    }
                    if ($campaign->is_email){
                        Mail::to($model->email)->send(new CampaignMail($campaign));
                    }
                    if (! $result) {
                        $errors = true;
                    }
                }
                $endTime = date('h:i:s');
                $execution = new Execution([
                    'date' => $dateTime->format('Y-m-d'),
                    'start_at' => $startTime,
                    'end_at' => $endTime,
                    'errors' => $errors ? 'There where errors in this execution. Check the logs.' : '[]',
                    'parameters' => (string) $campaign->parameters,
                ]);
                $campaign->executions()->save($execution);
            }
        }
    }
}
