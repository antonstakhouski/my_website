<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use GuzzleHttp\Client;
use App\Group;

class AdminController extends Controller
{
    /**
     * Returns view page for update groups.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function updateGroups()
    {
        return view('admin.groups.update');
    }

    /**
     * Update groups in DB.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function postUpdateGroups()
    {
        $groups = Group::select('number')->get();

        $client = new Client();
        $res = $client->request('GET', 'https://www.bsuir.by/schedule/rest/studentGroup');
        $xml_groups = ((new \SimpleXMLElement($res->getBody()->getContents())));

        $count = count($groups);

        for($j = 0; $j < $count; $j++) {
            for($i = 0; $i < count($xml_groups); $i++) {
                if(!strcmp($groups[$j]->number, $xml_groups->studentGroup[$i]->name->__toString()))
                {
                    unset($groups[$j]);
                    unset($xml_groups->studentGroup[$i]);
                    break;
                }
            }
        }

        $new_groups = [];

        for($i = 0; $i < count($xml_groups); $i++) {
            $new_groups[] = [
                'number' => $xml_groups->studentGroup[$i]->name->__toString(),
                'group_api_id' => $xml_groups->studentGroup[$i]->id->__toString(),
                'speciality_id' => 1,
                'faculty_id' => 1,
            ];
        }

        Group::insert($new_groups);

        return redirect()->back()->with('messages', ['Successfully updated']);
    }

}