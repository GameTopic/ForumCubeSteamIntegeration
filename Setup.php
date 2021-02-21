<?php

namespace ForumCube\SteamIntegeration;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    /** creating new table for steam game */
    public function installStep1() {
        try{
            $this->schemaManager()->createTable('xf_fc_steam_game', function(Create $table) {
                $table->addColumn('game_id', 'int',10)->autoIncrement();
                $table->addColumn('name', 'varchar',64);
                $table->addColumn('link', 'text');
                $table->addColumn('attachment', 'text');
                $table->addColumn('user_id', 'int',10);
                $table->addPrimaryKey('game_id');
            });
        }
        catch (Exception $exception){

        }
    }

    /** creating new table for steam workshop */
    public function installStep2() {
        try{
            $this->schemaManager()->createTable('xf_fc_steam_workshop', function(Create $table) {
                $table->addColumn('workshop_id', 'int',10)->autoIncrement();
                $table->addColumn('name', 'varchar',64);
                $table->addColumn('link', 'text');
                $table->addColumn('attachment', 'text');
                $table->addColumn('user_id', 'int',10);
                $table->addPrimaryKey('workshop_id');
            });
        }
        catch (Exception $exception){

        }
    }

    /** creating new table for steam friends */
    public function installStep3()
    {
        try{
            $this->schemaManager()->createTable('xf_fc_steam_friends', function(Create $table) {
                $table->addColumn('steam_friends_id', 'int',10)->autoIncrement();
                $table->addColumn('friend', 'varchar',64);
                $table->addColumn('user_id', 'int',10);
                $table->addPrimaryKey('steam_friends_id');
            });
        }
        catch (Exception $exception){

        }
    }

    /** creating new table for steam to access tab data */
    public function installStep4()
    {
        try{
            $this->schemaManager()->createTable('xf_fc_steam_tab_privacy', function(Create $table) {
                $table->addColumn('steam_privacy_id', 'int',10)->autoIncrement();
                $table->addColumn('user_id', 'int',10);
                $table->addColumn('visitor_username', 'varchar',64);
                $table->addPrimaryKey('steam_privacy_id');
            });
        }
        catch (Exception $exception){

        }
    }

    /** lets insert the new column in the user table */
    public function installStep5() {
        try {
            $this->schemaManager()->alterTable('xf_user_option', function(Alter $table) {
                $table->addColumn('fc_steam_data', 'int', 10)->setDefault(0);
            });
        } catch (Exception $exception) {

        }
        // TODO: Implement install() method.
    }

    /** lets add the more columns in the steam workshop */
    public function upgrade90010Step1()
    {
        $this->schemaManager()->alterTable('xf_fc_steam_workshop', function(Alter $table)
        {
            $table->addColumn('category', 'varchar',128);
            $table->addColumn('price','int',10)->setDefault(0);
            $table->addColumn('software','varchar',128);
            $table->addColumn('game','varchar',128);
        });
        // TODO: Implement upgrade() method.
    }

    /** lets add the more columns in the steam game */
    public function upgrade90010Step2()
    {
        $this->schemaManager()->alterTable('xf_fc_steam_game', function (Alter $table)
        {
            $table->addColumn('software','varchar',128);
            $table->addColumn('category','varchar',128);
            $table->addColumn('game_type','varchar',128);
            $table->addColumn('os','varchar',128);
            $table->addColumn('dlc','varchar',128);
            $table->addColumn('games','varchar',128);
            $table->addColumn('content_pack','varchar',128);
            $table->addColumn('price','int',10)->setDefault(0);
        });
    }

    /** lets insert the new column in the user table */
    public function upgrade90010Step3() {
        try {
            $this->schemaManager()->alterTable('xf_user_option', function(Alter $table) {
                $table->addColumn('fc_steam_site_branding', 'int', 10)->setDefault(0);
            });
        } catch (Exception $exception) {

        }
        // TODO: Implement install() method.
    }

    /**
     * Uninstall table steam game that was added
     * @return void
     */
    public function uninstallStep1() {
        $this->schemaManager()->dropTable('xf_fc_steam_game');
    }

    /**
     * Uninstall table steam workshop that was added
     * @return void
     */
    public function uninstallStep2() {
        $this->schemaManager()->dropTable('xf_fc_steam_workshop');
    }

    /**
     * Uninstall table steam friends that was added
     * @return void
     */
    public function uninstallStep3()
    {
        $this->schemaManager()->dropTable('xf_fc_steam_friends');
    }

    /**
     * Uninstall table steam tab privacy that was added
     * @return void
     */
    public function uninstallStep4()
    {
        $this->schemaManager()->dropTable('xf_fc_steam_tab_privacy');
    }

    /** removing the column on uninstall */
    public function uninstallStep5() {
        $this->schemaManager()->alterTable('xf_user_option', function(Alter $table) {
            $table->dropColumns('fc_steam_data');
            $table->dropColumns('fc_steam_site_branding');
        });
    }

}