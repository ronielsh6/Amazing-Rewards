<?php

namespace App\Mail;

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PhpParser\Node\Scalar\String_;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $link = $this->getLinkByDeep($this->campaign->deep_link);
        $data = [
            'campaign' => $this->campaign,
            'link'   => $link
        ];
        $this->subject($this->campaign->title);

        return $this->markdown('email.emailCampaign')->with($data);
    }

    private function getLinkByDeep($deep_link): string
    {
        $link = "";
        switch ($deep_link) {
            case('shop_screen'):
                $link = 'https://amzn.to/3hx99Sh';
                break;
            case('fluent_sephora'):
                $link = 'https://spnccrzone.com/?bbz=ogwHOldKbC1TyGDK5gYqlnYI8X3RA6DwvQJDRoz7h5U%3d&s1=';
                break;
            case('fluent_amazon'):
                $link = 'https://spnccrzone.com/?TTT=0x3mnMR0AgdTmCI%2fkD4C62JPWQ5jEzxHvQJDRoz7h5U%3d&s1=';
                break;
            case('fluent_cash'):
                $link = 'https://spnccrzone.com/?es4v=6YprMP%2f%2bMldresVZVdqqZuyB3zgAgzoCvQJDRoz7h5U%3d&s1=';
                break;
            case('app_update'):
                $link = 'https://play.google.com/store/apps/details?id=my.amazing.rewards';
                break;

            default:
                $link = 'https://myamazingrewards.com';
        }

        return $link;
    }
}
