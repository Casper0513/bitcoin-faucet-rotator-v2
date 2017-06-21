<?php
use App\Models\Faucet;
use App\Models\User;

/**
 * Created by PhpStorm.
 * User: robattfield
 * Date: 02-Mar-2015
 * Time: 22:38
 */

class FaucetsTableSeeder extends BaseSeeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->csv_to_array(base_path() . '/database/seeds/csv_files/faucets.csv', ';');
        $user = User::where('user_name', env('ADMIN_USERNAME'))->first();

        foreach ($data as $d) {
            $url = $d['url'];
            try {
                $faucet = new Faucet([
                    'name' => $d['name'],
                    'url' => $url,
                    'interval_minutes' => (int)$d['interval_minutes'],
                    'min_payout' => (int)$d['min_payout'],
                    'max_payout' => (int)$d['max_payout'],
                    'has_ref_program' => (int)$d['has_ref_program'],
                    'ref_payout_percent' => (int)$d['ref_payout_percent'],
                    'comments' => $d['comments'],
                    'is_paused' => (int)$d['is_paused'],
                    'meta_title' => $d['meta_title'],
                    'meta_description' => $d['meta_description'],
                    'meta_keywords' => $d['meta_keywords'],
                    'has_low_balance' => (int)$d['has_low_balance'],
                ]);
                $faucet->save();

                $referralCode = $d['referral_code'];
                $this->command->info(
                    "User ID: " . $user->id .
                    ", Faucet ID:  " . $faucet->id .
                    ", Faucet Name: " . $faucet->name .
                    ", Referral Code: " . $referralCode
                );
                $faucet->users()->attach($user->id, ['faucet_id' => $faucet->id, 'referral_code' => $referralCode]);

            } catch (Exception $e) {
                error_log($e->getMessage());
            }
        }
    }
}
