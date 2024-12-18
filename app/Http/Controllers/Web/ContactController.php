<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contactSettings = getContactPageSettings();

        $seoSettings = getSeoMetas('contact');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.contact_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.contact_page_title');
        $pageRobot = getPageRobot('contact');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'contactSettings' => $contactSettings
        ];

        return view('web.default.pages.contact', $data);
    }
    
    public function modal(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'string|email',
            'phone' => 'required|numeric',
            'message' => 'required|string',
        ]);
        $data = $request->all();
        if (!isset($data['phone']) || !$data['phone']) {
    $data['phone'] = '00000'; // Make sure to use a string if it's a phone number.
}
        unset($data['_token']);
        $data['created_at'] = time();


        $notifyOptions = [
            '[c.u.title]' => $data['subject'],
            '[u.name]' => $data['name'],
            '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            '[c.u.message]' => $data['message'],
        ];

        sendNotification('contact_message_submission_for_admin', $notifyOptions, 1);

        sendNotificationToEmail('contact_message_submission', $notifyOptions, $data['email']);

        return back()->with(['msg' => trans('site.contact_store_success')]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'numeric',
            'subject' => 'required|string',
            'message' => 'required|string',
            'captcha' => 'required|captcha',
        ]);
        $data = $request->all();
        if (!isset($data['phone']) || !$data['phone']) {
    $data['phone'] = '00000'; // Make sure to use a string if it's a phone number.
}
        unset($data['_token']);
        $data['created_at'] = time();

        Contact::create($data);

        $notifyOptions = [
            '[c.u.title]' => $data['subject'],
            '[u.name]' => $data['name'],
            '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            '[c.u.message]' => $data['message'],
        ];

        sendNotification('contact_message_submission_for_admin', $notifyOptions, 1);

        sendNotificationToEmail('contact_message_submission', $notifyOptions, $data['email']);

        return back()->with(['msg' => trans('site.contact_store_success')]);
    }
}
