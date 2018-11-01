<?php

use yii\db\Migration;

/**
 * Class m181025_181406_feedback_init
 *
 * ALTER DATABASE <database_name> CHARACTER SET utf8 COLLATE utf8_unicode_ci;
 */
class m181025_181406_feedback_init extends Migration
{
    /**
    * @inheritdoc
    */
    public function safeUp()
    {
        /**
         * feedback table
         */
        $this->createTable('{{%feedback}}', [
            'id'             => $this->primaryKey(),
            'program_name'   => $this->string(50),
            'type'           => $this->smallInteger(),
            'feedback_order' => $this->integer(),
            'editable'       => $this->boolean(),
            'visible'        => $this->boolean(),
        ]);

        /**
         * feedback_config table
         */
        $this->createTable('{{%feedback_config}}', [
            'id' => $this->primaryKey(),
        ]);

        $this->insert('{{%feedback_config}}', [
            'id' => 1,
        ]);

        /**
         * pages_names_translates table
         */
        $this->createTable('{{%feedback_names_translates}}', [
            'id'                 => $this->primaryKey(),
            'feedback_id'        => $this->integer(),
            'common_language_id' => $this->integer(),
            'name'               => $this->string(),
            'description'        => $this->string(),
        ]);

        $this->addForeignKey('feedback_names_translates-to-feedback',
            '{{%feedback_names_translates}}',
            'feedback_id',
            '{{%feedback}}',
            'id'
        );

        $this->addForeignKey('feedback_names_translates-to-common_languages',
            '{{%feedback_names_translates}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );

        /**
         * feedback_stages table
         */
        $this->createTable('{{%feedback_stages}}', [
            'id'                                 => $this->primaryKey(),
            'feedback_id'                        => $this->integer(),
            'program_name'                       => $this->string(50),
            'stage_order'                        => $this->integer(),
            'editable'                           => $this->boolean(),
            'visible'                            => $this->boolean(),
            'stage_field_template_reference'     => $this->string(),
            'stage_file_template_reference'      => $this->string(),
            'stage_image_template_reference'     => $this->string(),
            'stage_condition_template_reference' => $this->string(),
            'stage_field_reference'              => $this->string(),
            'stage_file_reference'               => $this->string(),
            'stage_image_reference'              => $this->string(),
            'stage_condition_reference'          => $this->string(),
            'input_field_template_reference'     => $this->string(),
            'input_file_template_reference'      => $this->string(),
            'input_image_template_reference'     => $this->string(),
            'input_condition_template_reference' => $this->string(),
        ]);

        $this->addForeignKey('feedback_stages-to-feedback',
            '{{%feedback_stages}}',
            'feedback_id',
            '{{%feedback}}',
            'id'
        );

        /**
         * feedback_stages_names_translates table
         */
        $this->createTable('{{%feedback_stages_names_translates}}', [
            'id'                 => $this->primaryKey(),
            'stage_id'           => $this->integer(),
            'common_language_id' => $this->integer(),
            'name'               => $this->string(),
            'description'        => $this->string(),
        ]);


        $this->addForeignKey('feedback_stages_names_translates-to-feedback_stages',
            '{{%feedback_stages_names_translates}}',
            'stage_id',
            '{{%feedback_stages}}',
            'id'
        );

        $this->addForeignKey('feedback_stages_names_translates-to-common_languages',
            '{{%feedback_stages_names_translates}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );

        /**
         * feedback_stages_names_translates table
         */
        $this->createTable('{{%feedback_states}}', [
            'id'                         => $this->primaryKey(),
            'stage_id'                   => $this->integer(),
            'input_fields_reference'     => $this->string(),
            'input_files_reference'      => $this->string(),
            'input_images_reference'     => $this->string(),
            'input_conditions_reference' => $this->string(),
            'created_at'                 => $this->integer(),
            'updated_at'                 => $this->integer(),
        ]);

        $this->addForeignKey('feedback_states-to-feedback_stages',
            '{{%feedback_states}}',
            'stage_id',
            '{{%feedback_stages}}',
            'id'
        );

        /**
         * feedback_input_fields_states table
         */
        $this->createTable('{{%feedback_input_fields_states}}', [
            'id'                        => $this->primaryKey(),
            'state_id'                  => $this->integer(),
            'common_fields_template_id' => $this->integer(),
        ]);

        $this->addForeignKey('feedback_input_fields_states-to-feedback_states',
            '{{%feedback_input_fields_states}}',
            'state_id',
            '{{%feedback_states}}',
            'id'
        );

        /**
         * feedback_input_files_states table
         */
        $this->createTable('{{%feedback_input_files_states}}', [
            'id'                       => $this->primaryKey(),
            'state_id'                 => $this->integer(),
            'common_files_template_id' => $this->integer(),
        ]);

        $this->addForeignKey('feedback_input_files_states-to-feedback_states',
            '{{%feedback_input_files_states}}',
            'state_id',
            '{{%feedback_states}}',
            'id'
        );

        /**
         * feedback_input_images_states table
         */
        $this->createTable('{{%feedback_input_images_states}}', [
            'id'                         => $this->primaryKey(),
            'state_id'                   => $this->integer(),
            'common_images_templates_id' => $this->integer(),
        ]);

        $this->addForeignKey('feedback_input_images_states-to-feedback_states',
            '{{%feedback_input_images_states}}',
            'state_id',
            '{{%feedback_states}}',
            'id'
        );

        /**
         * feedback_input_conditions_states table
         */
        $this->createTable('{{%feedback_input_conditions_states}}', [
            'id'                           => $this->primaryKey(),
            'state_id'                     => $this->integer(),
            'common_condition_template_id' => $this->integer(),
        ]);

        $this->addForeignKey('feedback_input_conditions_states-to-feedback_states',
            '{{%feedback_input_conditions_states}}',
            'state_id',
            '{{%feedback_states}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('feedback_input_conditions_states-to-feedback_states',
            '{{%feedback_input_conditions_states}}');
        $this->dropTable('{{%feedback_input_conditions_states}}');

        $this->dropForeignKey('feedback_input_images_states-to-feedback_states',
            '{{%feedback_input_images_states}}');
        $this->dropTable('{{%feedback_input_images_states}}');

        $this->dropForeignKey('feedback_input_files_states-to-feedback_states',
            '{{%feedback_input_files_states}}');
        $this->dropTable('{{%feedback_input_files_states}}');

        $this->dropForeignKey('feedback_input_fields_states-to-feedback_states',
            '{{%feedback_input_fields_states}}');
        $this->dropTable('{{%feedback_input_fields_states}}');

        $this->dropForeignKey('feedback_states-to-feedback_stages',
            '{{%feedback_states}}');
        $this->dropTable('{{%feedback_states}}');

        $this->dropForeignKey('feedback_stages_names_translates-to-common_languages',
            '{{%feedback_stages_names_translates}}');
        $this->dropForeignKey('feedback_stages_names_translates-to-feedback_stages',
            '{{%feedback_stages_names_translates}}');
        $this->dropTable('{{%feedback_stages_names_translates}}');

        $this->dropForeignKey('feedback_stages-to-feedback',
            '{{%feedback_stages}}');
        $this->dropTable('{{%feedback_stages}}');

        $this->dropForeignKey('feedback_names_translates-to-common_languages',
            '{{%feedback_names_translates}}');
        $this->dropForeignKey('feedback_names_translates-to-feedback',
            '{{%feedback_names_translates}}');
        $this->dropTable('{{%feedback_names_translates}}');

        $this->dropTable('{{%feedback_config}}');

        $this->dropTable('{{%feedback}}');
    }
}
