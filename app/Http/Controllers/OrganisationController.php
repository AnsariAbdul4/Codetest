<?php

declare(strict_types=1);

namespace App\Http\Controllers;
// namespace App\Transformers;

use App\Organisation;
use App\Services\OrganisationService;
use App\Transformers\OrganisationTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\User;
use Illuminate\Support\Facades\Mail;

/**
 * Class OrganisationController
 * @package App\Http\Controllers
 */
class OrganisationController extends ApiController
{
    /**
     * @param OrganisationService $service
     *
     * @return JsonResponse
     */
    public function store(OrganisationService $service): JsonResponse
    {
        /** @var Organisation $organisation */
        $organisation = $service->createOrganisation($this->request->all());
        return $this
            ->transformItem('organisation', $organisation, ['user'])
            ->respond();
    }

    public function listAll(OrganisationService $service)
    {
        
        /** @var Organisation $organisation */
        $organisation = $service->getOrganisationList($this->request->all());
        
        return response()->json($organisation);
        
    }

    /**
     * @param OrganisationService $service
     *
     * @return JsonResponse
     */
    public function create(OrganisationService $service): JsonResponse
    {
        $users = DB::table('users')->select(DB::raw('GROUP_CONCAT(id) as users'))->first();
        $usersArray = !is_null($users) ? explode(',',$users->users) : []; //dd($users);
        
        // Validate 
        $validatedDate = $this->request->validate(
            [
                'name' => ['required','string'],
                'owner_user_id' => ['required','numeric', Rule::In($usersArray)],
                'trial_end' => 'required|date_format:Y-m-d H:i:s|after:30 days',
                'subscribed' => [Rule::in(['0', '1'])]
            ]
        );

        $owner_user_id = $this->request->get('owner_user_id');
        $user = User::find($owner_user_id);
        $requestArray = $this->request->all();
        $requestArray['user'] = $user;
        $to_email = $user->email;

        $data = array('name'=>$user->name);
        
        Mail::send(['text'=>'mail'], $data, function($message) use ($to_email, $requestArray) {
            $message->to($to_email, 'Organisation Created')->subject
                ('Orgnisation "'.$requestArray['name'].'" has been created.');
            $message->from('abdulrahemanmca@gmail.com','Abdul Raheman Ansari');
        });

        
        /** @var Organisation $organisation */
        $organisation = $service->createOrganisation($requestArray);
        
        return $this
            ->transformItem('organisation', $organisation, $user)
            ->respond();        
    }
}
