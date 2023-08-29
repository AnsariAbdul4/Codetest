<?php

declare(strict_types=1);

namespace App\Services;

use App\Organisation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;


/**
 * Class OrganisationService
 * @package App\Services
 */
class OrganisationService
{
    /**
     * @param array $attributes
     *
     * @return Organisation
     */
    public function createOrganisation(array $attributes): Organisation
    {
        $organisation = new Organisation();
        $organisation->name = $attributes['name'];
        $organisation->owner_user_id = $attributes['owner_user_id'];
        $organisation->trial_end = $attributes['trial_end'];
        $organisation->subscribed = $attributes['subscribed']??'0';
        $organisation->save();
        $organisation->user = $attributes['user'];
        return $organisation;
    }

    public function getOrganisationList(array $attributes): Organisation
    {
        $filter = $attributes['filter'] ?? false;

        $Organisations = DB::table('organisations')->leftjoin('users as u','u.id','organisations.owner_user_id')->select('organisations.*','u.name as owner_name','u.email as owner_email')->get();
        $collection = collect($Organisations);
        if (isset($filter)) {
            if ($filter == 'subbed') {
                $newColect = $collection->filter(function ($value){
                    return $value->subscribed == 1;
                });
            }else if ($filter == 'trail') {
                $newColect = $collection->filter(function ($value){
                    return $value->subscribed == 0;
                });
            }else{
                $newColect = $collection;
            }
        }
        $organisation = new Organisation();
        $organisation->result = $newColect;
        return $organisation;
    }

}
