<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCampaign;
use App\Models\Campaign;
use App\Models\Execution;
use App\Models\User;
use App\Services\CloudMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use mysql_xdevapi\Table;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role');
    }

    public function getCampaigns(Request $request)
    {
        return view('campaigns');
    }

    public function getCampaignsList(Request $request)
    {
        $start = $request->get('start');
        $page = $request->get('length');
        $orderElement = $request->get('order')[0];
        $orderDir = $orderElement['dir'];
        $column = $request->get('columns')[$orderElement['column']]['data'];
        $campaignsQuery = Campaign::with('executions');
        $totalRecordsFiltered = $campaignsQuery->get()->count();

        if ($start > 0) {
            $offset = ($start / $page);
            $campaignsQuery->offset($offset * $page);
        }

        if ($column !== 'executions') {
            $campaignsQuery->orderBy($column, $orderDir);
        }
        $campaignsQuery->limit($page);
        $campaigns = $campaignsQuery->get()->toArray();

        return response()->json([
            'data' => $campaigns,
            'recordsTotal' => $totalRecordsFiltered,
            'recordsFiltered' => $totalRecordsFiltered,
        ]);
    }

    public function createCampaign(Request $request)
    {
        $campaign = new Campaign($request->all());
        $url = URL::to('/').''.Storage::url($request->image);
        $campaign->image = $url;
        $campaign->save();
        if ($campaign) {
            return response()->json([
                'code' => 200,
                'message' => 'Campaign created successfully',
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Error creating campaign',
        ]);
    }

    public function updateCampaign(Request $request)
    {
        $id = $request->get('campaign_id');
        $campaignData = \array_filter($request->all(), static function ($item) {
            return $item !== '_token' && $item !== 'campaign_id';
        }, ARRAY_FILTER_USE_KEY);
        $result = Campaign::where('id', $id)
            ->update($campaignData);

        if ($result > 0) {
            return response()->json([
                'code' => 200,
                'message' => 'Campaign updated successfully',
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Error updating campaign',
        ]);
    }

    public function deleteCampaign(Request $request)
    {
    }

    public function executeCampaign(Request $request)
    {
        $id = $request->get('id');
        $campaign = Campaign::find($id);

//        select * from `users` WHERE id IN (SELECT DISTINCT(`users`.id) from `users` left join `gift_card` on `users`.`id` = `gift_card`.`owner` where users.points = 90000)

        $subQuery = User::with('getGiftCards')
            ->select('id')
            ->whereRaw($campaign->parameters)
            ->distinct();
        $users = User::whereRaw("users.id in ({$subQuery->toSql()})")
            ->get();
        $dateTime = new \DateTime('now');

        $errors = false;
        $startTime = date('h:i:s');
        foreach ($users as $user) {
            ProcessCampaign::dispatch(new CloudMessages(), $user, $campaign);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Campaign executed successfully',
        ]);
    }

    public function queryImpact(Request $request)
    {
        $query = $request->get('query');
        $users = DB::table('users')->leftJoin('gift_card', 'users.id', '=', 'gift_card.owner')
            ->whereRaw($query)
            ->get()->count();

        return response()->json([
            'code' => 200,
            'total' => $users,
        ]);
    }
}
