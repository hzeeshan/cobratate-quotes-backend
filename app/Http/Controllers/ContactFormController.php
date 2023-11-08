<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactFormSubmission;

class ContactFormController extends Controller
{
    public function store(Request $request)
    {
        try {

            $data = $request->data;

            ContactFormSubmission::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
            ]);

            Mail::to('hafizzeeshan619@gmail.com')->send(new ContactFormMail($data));

            return response()->json(['success' => true, 'message' => 'Thank you for contacting us!']);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred. Please try again later.']);
        }
    }
}
