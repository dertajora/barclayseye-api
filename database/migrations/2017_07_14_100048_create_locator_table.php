<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branchs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('branch_name');
            $table->string('address');
            $table->decimal('lat', 11, 8);
            $table->decimal('longi', 11, 8);
            $table->integer('phone');
            $table->tinyInteger('type')->comment('1 Branch, 2 ATM');
        });


        $data_branch = array(
            array('branch_name' => 'Manchester M25 1AX', 'address' => '460 Bury New Road' , 'lat' => 53.533231, 'longi'=> -2.284868, 'phone' => 03457345345, 'type' => 1),
            array('branch_name' => 'Manchester M2 3HQ', 'address' => '51 Mosley Street' , 'lat' => 53.480186, 'longi'=> -2.240882, 'phone' => 03457345345, 'type' => 1),
            array('branch_name' => 'Manchester M1 1PD', 'address' => '86-88 Market Street' , 'lat' => 53.482326, 'longi'=> -2.240630, 'phone' => 03457345345, 'type' => 1),
            array('branch_name' => 'Manchester M2 7PW', 'address' => "17 St. Ann's Square" , 'lat' => 53.482109, 'longi'=> -2.245414, 'phone' => 03457345345, 'type' => 1),
            array('branch_name' => 'Manchester M13 9NG', 'address' => '320/322 Oxford Road' , 'lat' => 53.461754, 'longi'=> -2.229447, 'phone' => 03457345345, 'type' => 1),
            array('branch_name' => 'Manchester M12 4JH', 'address' => 'Longsight Shopping Centre' , 'lat' => 53.457270, 'longi'=> -2.200347, 'phone' => 03457345345, 'type' => 1),
            array('branch_name' => 'Manchester M21 9AL', 'address' => '587 Wilbraham Road' , 'lat' => 53.442459, 'longi'=> -2.277779, 'phone' => 03457345345, 'type' => 1)
        );

        // Insert some stuff
        DB::table('branchs')->insert($data_branch);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
