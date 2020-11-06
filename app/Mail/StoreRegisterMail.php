<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StoreRegisterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $html;
    /**
     * Create a new message instance.
     *
     * @param $html
     */
    public function __construct($html)
    {
        $this->html = $html;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $html = $this->html;
        return $this->view('emails.general',compact('html'));
    }
}
