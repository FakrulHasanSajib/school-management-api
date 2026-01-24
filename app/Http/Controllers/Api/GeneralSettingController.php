<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\File; // ফাইল ডিলিট করার জন্য

class GeneralSettingController extends Controller
{
    // ১. সেটিংস ডাটা দেখা
    public function index()
    {
        // সবসময় প্রথম রো (First Row) টি রিটার্ন করবে
        $settings = GeneralSetting::first();
        return response()->json([
            'status' => true,
            'data' => $settings
        ]);
    }

    // ২. সেটিংস আপডেট করা (লোগো সহ)
    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'principal_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ডাটাবেসে আগে কোনো সেটিংস আছে কি না চেক করা
        $settings = GeneralSetting::first();
        if (!$settings) {
            $settings = new GeneralSetting(); // না থাকলে নতুন বানাবে
        }

        // সাধারণ তথ্য আপডেট
        $settings->school_name = $request->school_name;
        $settings->school_address = $request->school_address;
        $settings->phone = $request->phone;
        $settings->email = $request->email;

        // লোগো আপলোড লজিক
        if ($request->hasFile('school_logo')) {
            // পুরানো লোগো ডিলিট করা (যদি থাকে)
            if ($settings->school_logo && File::exists(public_path($settings->school_logo))) {
                File::delete(public_path($settings->school_logo));
            }
            // নতুন লোগো সেভ
            $file = $request->file('school_logo');
            $filename = time() . '_logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/settings'), $filename);
            $settings->school_logo = 'uploads/settings/' . $filename;
        }

        // স্বাক্ষর আপলোড লজিক
        if ($request->hasFile('principal_signature')) {
            if ($settings->principal_signature && File::exists(public_path($settings->principal_signature))) {
                File::delete(public_path($settings->principal_signature));
            }
            $file = $request->file('principal_signature');
            $filename = time() . '_sign.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/settings'), $filename);
            $settings->principal_signature = 'uploads/settings/' . $filename;
        }

        $settings->save();

        return response()->json([
            'status' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings
        ]);
    }
}