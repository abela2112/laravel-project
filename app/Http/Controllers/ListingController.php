<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{
    //get all the listing
    public static function index()
    {
        return view('Listing.index', ['listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)]);
    }
    //show a single listing page
    public static function show(Listing $listing)
    {
        return view('Listing.show', ['listing' => $listing]);
    }

    // create a new listing page
    public static function create()
    {
        return view('Listing.create');
    }
    public static function edit(Listing $listing)
    {
        return view('Listing.edit', ['listing' => $listing]);
    }

    //store a new listing
    public static function store(Request $request)
    {
        // dd($request->file());
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'description' => 'required',
            'email' => ['required', 'email'],
            'website' => 'required',
            'location' => 'required',
            'tags' => 'required',

        ]);
        $formFields['user_id'] = auth()->id();
        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }
        // dd(auth()->id());


        Listing::create($formFields);
        return redirect('/')->with('message', 'Listing created successfully');
    }
    public static function update(Request $request, Listing $listing)
    {
        //make sure the listings are belong to the logged in user 
        if($listing->user_id !=auth()->id()){
            abort(403,'UnAuthorized Error');
        } 
        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'description' => 'required',
            'email' => ['required', 'email'],
            'website' => 'required',
            'location' => 'required',
            'tags' => 'required',

        ]);
        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);
        return back()->with('message', "Listing updated successfully");
    }

    public function manage(){
        return view('Listing.manage',['listings'=>auth()->user()->listings()->get()]);
    }

    public function destroy(Listing $listing) {
        // Make sure logged in user is owner
        if($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }
        
        if($listing->logo && Storage::disk('public')->exists($listing->logo)) {
            Storage::disk('public')->delete($listing->logo);
        }
        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted successfully');
    }
}
