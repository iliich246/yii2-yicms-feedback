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
            'id'                                 => $this->primaryKey(),
            'program_name'                       => $this->string(50),
            'type'                               => $this->smallInteger(),
            'feedback_order'                     => $this->integer(),
            'editable'                           => $this->boolean(),
            'active'                             => $this->boolean(),
            'admin_can_edit_fields'              => $this->boolean(),
            'admin_can_delete_states'            => $this->boolean(),
            'count_states_on_page'               => $this->integer(),
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

        /**
         * feedback_config table
         */
        $this->createTable('{{%feedback_config}}', [
            'id'               => $this->primaryKey(),
            'is_generated'     => $this->boolean(),
            'strongGenerating' => $this->boolean(),
        ]);

        $this->insert('{{%feedback_config}}', [
            'id'               => 1,
            'isGenerated'      => false,
            'strongGenerating' => false

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
         * feedback_stages_names_translates table
         */
        $this->createTable('{{%feedback_states}}', [
            'id'                         => $this->primaryKey(),
            'feedback_id'                => $this->integer(),
            'input_fields_reference'     => $this->string(),
            'input_files_reference'      => $this->string(),
            'input_images_reference'     => $this->string(),
            'input_conditions_reference' => $this->string(),
            'is_handled'                 => $this->smallInteger(),
            'created_at'                 => $this->integer(),
            'updated_at'                 => $this->integer(),
        ]);

        $this->addForeignKey('feedback_states-to-feedback',
            '{{%feedback_states}}',
            'feedback_id',
            '{{%feedback}}',
            'id'
        );

        //////////////////////////////////////////////////////////////////
        // Input fields functionality
        //////////////////////////////////////////////////////////////////
        /**
         * feedback_input_fields_templates table
         */
        $this->createTable('{{%feedback_input_fields_templates}}', [
            'id'                             => $this->primaryKey(),
            'input_field_template_reference' => $this->string(),
            'validator_reference'            => $this->string(),
            'program_name'                   => $this->string(50),
            'input_field_order'              => $this->integer(),
            'editable'                       => $this->boolean(),
            'active'                         => $this->boolean(),
        ]);

        $this->createIndex(
            'input_field_template_reference-index',
            '{{%feedback_input_fields_templates}}',
            'input_field_template_reference'
        );

        /**
         * common_field_names table
         */
        $this->createTable('{{%feedback_input_field_templates_names}}', [
            'id'                                => $this->primaryKey(),
            'feedback_input_fields_template_id' => $this->integer(),
            'common_language_id'                => $this->integer(),
            'dev_name'                          => $this->string(),
            'dev_description'                   => $this->string(),
            'admin_name'                        => $this->string(),
            'admin_description'                 => $this->string(),
        ]);

        $this->addForeignKey('input_field_templates_names-to-input_fields_templates',
            '{{%feedback_input_field_templates_names}}',
            'feedback_input_fields_template_id',
            '{{%feedback_input_fields_templates}}',
            'id'
        );

        $this->addForeignKey('feedback_input_field_templates_names-to-common_languages',
            '{{%feedback_input_field_templates_names}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );

        /**
         * feedback_input_fields_represents table
         */
        $this->createTable('{{%feedback_input_fields_represents}}', [
            'id'                                => $this->primaryKey(),
            'feedback_input_fields_template_id' => $this->integer(),
            'input_field_reference'             => $this->string(),
            'value'                             => $this->text()->defaultValue(null),
        ]);

        $this->createIndex(
            'input_field_reference-index',
            '{{%feedback_input_fields_represents}}',
            'input_field_reference'
        );

        $this->addForeignKey('input_fields_represents-to-input_fields_templates',
            '{{%feedback_input_fields_represents}}',
            'feedback_input_fields_template_id',
            '{{%feedback_input_fields_templates}}',
            'id'
        );

        //////////////////////////////////////////////////////////////////
        // Input Files functionality
        //////////////////////////////////////////////////////////////////
        /**
         * feedback_input_files_templates table
         */
        $this->createTable('{{%feedback_input_files_templates}}', [
            'id'                            => $this->primaryKey(),
            'input_file_template_reference' => $this->string(),
            'validator_reference'           => $this->string(),
            'program_name'                  => $this->string(50),
            'type'                          => $this->smallInteger(),
            'input_file_order'              => $this->integer(),
            'editable'                      => $this->boolean(),
            'active'                        => $this->boolean(),
            'max_files'                     => $this->integer(),
        ]);

        $this->createIndex(
            'input_file_template_reference-index',
            '{{%feedback_input_files_templates}}',
            'input_file_template_reference'
        );

        /**
         * common_file_names table
         */
        $this->createTable('{{%feedback_input_file_names}}', [
            'id'                               => $this->primaryKey(),
            'feedback_input_files_template_id' => $this->integer(),
            'common_language_id'               => $this->integer(),
            'dev_name'                          => $this->string(),
            'dev_description'                   => $this->text(),
            'admin_name'                        => $this->string(),
            'admin_description'                 => $this->text(),
        ]);

        $this->addForeignKey('feedback_input_file_names-to-feedback_input_files_templates',
            '{{%feedback_input_file_names}}',
            'feedback_input_files_template_id',
            '{{%feedback_input_files_templates}}',
            'id'
        );

        $this->addForeignKey('feedback_input_file_names-to-common_languages',
            '{{%feedback_input_file_names}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );

        /**
         * feedback_input_files table
         */
        $this->createTable('{{%feedback_input_files}}', [
            'id'                               => $this->primaryKey(),
            'feedback_input_files_template_id' => $this->integer(),
            'input_file_reference'             => $this->string(),
            'system_name'                      => $this->string(),
            'original_name'                    => $this->string(),
            'size'                             => $this->integer(),
            'type'                             => $this->string(),
            'created_at'                       => $this->integer(),
            'updated_at'                       => $this->integer(),
        ]);

        $this->createIndex(
            'input_file_reference-index',
            '{{%feedback_input_files}}',
            'input_file_reference'
        );

        $this->addForeignKey('feedback_input_files-to-feedback_input_files_templates',
            '{{%feedback_input_files}}',
            'feedback_input_files_template_id',
            '{{%feedback_input_files_templates}}',
            'id'
        );

        //////////////////////////////////////////////////////////////////
        // Input images functionality
        //////////////////////////////////////////////////////////////////
        /**
         * feedback_input_images_templates table
         */
        $this->createTable('{{%feedback_input_images_templates}}', [
            'id'                             => $this->primaryKey(),
            'input_image_template_reference' => $this->string(),
            'validator_reference'            => $this->string(),
            'program_name'                   => $this->string(50),
            'type'                           => $this->smallInteger(),
            'input_image_order'              => $this->integer(),
            'active'                         => $this->boolean(),
            'editable'                       => $this->boolean(),
            'max_images'                     => $this->smallInteger(),
        ]);

        $this->createIndex(
            'feedback_input_images_templates-index',
            '{{%feedback_input_images_templates}}',
            'input_image_template_reference'
        );

        /**
         * feedback_input_image_names table
         */
        $this->createTable('{{%feedback_input_image_names}}', [
            'id'                                => $this->primaryKey(),
            'feedback_input_images_template_id' => $this->integer(),
            'common_language_id'                => $this->integer(),
            'dev_name'                          => $this->string(),
            'dev_description'                   => $this->text(),
            'admin_name'                        => $this->string(),
            'admin_description'                 => $this->text(),
        ]);

        $this->addForeignKey('input_image_names-to-input_images_templates',
            '{{%feedback_input_image_names}}',
            'feedback_input_images_template_id',
            '{{%feedback_input_images_templates}}',
            'id'
        );

        $this->addForeignKey('feedback_input_image_names-to-common_languages',
            '{{%feedback_input_image_names}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );

        /**
         * feedback_input_images table
         */
        $this->createTable('{{%feedback_input_images}}', [
            'id'                                => $this->primaryKey(),
            'feedback_input_images_template_id' => $this->integer(),
            'input_image_reference'             => $this->string(),
            'system_name'                       => $this->string(),
            'original_name'                     => $this->string(),
            'size'                              => $this->integer(),
            'type'                              => $this->string(),
            'created_at'                        => $this->integer(),
            'updated_at'                        => $this->integer(),
        ]);

        $this->createIndex(
            'feedback_input_images-index',
            '{{%feedback_input_images}}',
            'input_image_reference'
        );

        $this->addForeignKey('input_images-to-input_images_templates',
            '{{%feedback_input_images}}',
            'feedback_input_images_template_id',
            '{{%feedback_input_images_templates}}',
            'id'
        );

        //////////////////////////////////////////////////////////////////
        // Input conditions functionality
        //////////////////////////////////////////////////////////////////
        /**
         * feedback_input_conditions_templates table
         */
        $this->createTable('{{%feedback_input_conditions_templates}}', [
            'id'                                 => $this->primaryKey(),
            'input_condition_template_reference' => $this->string(),
            'program_name'                       => $this->string(50),
            'type'                               => $this->smallInteger(),
            'checkbox_state_default'             => $this->boolean(),
            'input_condition_order'              => $this->integer(),
            'validator_reference'                => $this->string(),
            'editable'                           => $this->boolean(),
            'active'                             => $this->boolean(),
        ]);

        $this->createIndex(
            'feedback_input_conditions_templates-index',
            '{{%feedback_input_conditions_templates}}',
            'input_condition_template_reference'
        );

        /**
         * feedback_input_conditions_names table
         */
        $this->createTable('{{%feedback_input_conditions_names}}', [
            'id'                                   => $this->primaryKey(),
            'input_condition_template_template_id' => $this->integer(),
            'common_language_id'                   => $this->integer(),
            'dev_name'                             => $this->string(),
            'dev_description'                      => $this->text(),
            'admin_name'                           => $this->string(),
            'admin_description'                    => $this->text(),
        ]);

        $this->addForeignKey('input_conditions_names-to-input_conditions_templates',
            '{{%feedback_input_conditions_names}}',
            'input_condition_template_template_id',
            '{{%feedback_input_conditions_templates}}',
            'id'
        );

        $this->addForeignKey('feedback_input_conditions_templates-to-common_languages',
            '{{%feedback_input_conditions_names}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );

        /**
         * feedback_input_conditions table
         */
        $this->createTable('{{%feedback_input_conditions}}', [
            'id'                                   => $this->primaryKey(),
            'input_condition_template_template_id' => $this->integer(),
            'input_condition_reference'            => $this->string(),
            'feedback_value_id'                    => $this->integer(),
            'checkbox_state'                       => $this->boolean(),
        ]);

        $this->createIndex(
            'feedback_input_conditions-index',
            '{{%feedback_input_conditions}}',
            'input_condition_reference'
        );

        $this->addForeignKey('input_conditions-to-input_conditions_templates',
            '{{%feedback_input_conditions}}',
            'input_condition_template_template_id',
            '{{%feedback_input_conditions_templates}}',
            'id'
        );

        /**
         * feedback_input_conditions_values table
         */
        $this->createTable('{{%feedback_input_conditions_values}}', [
            'id'                                   => $this->primaryKey(),
            'input_condition_template_template_id' => $this->integer(),
            'value_name'                           => $this->string(),
            'input_condition_value_order'          => $this->integer(),
            'is_default'                           => $this->boolean(),
        ]);

        $this->addForeignKey('input_conditions_values-to-input_conditions_templates',
            '{{%feedback_input_conditions_values}}',
            'input_condition_template_template_id',
            '{{%feedback_input_conditions_templates}}',
            'id'
        );

        $this->addForeignKey('input_conditions-to-input_conditions_values',
            '{{%feedback_input_conditions}}',
            'feedback_value_id',
            '{{%feedback_input_conditions_values}}',
            'id'
        );

        /**
         * feedback_input_conditions_value_names table
         */
        $this->createTable('{{%feedback_input_conditions_value_names}}', [
            'id'                                => $this->primaryKey(),
            'feedback_input_condition_value_id' => $this->integer(),
            'common_language_id'                => $this->integer(),
            'name'                              => $this->string(),
            'description'                       => $this->string(),
        ]);

        $this->addForeignKey('input_conditions_value_names-to-input_conditions_values',
            '{{%feedback_input_conditions_value_names}}',
            'feedback_input_condition_value_id',
            '{{%feedback_input_conditions_values}}',
            'id'
        );

        $this->addForeignKey('input_conditions_value_names-to-common_languages',
            '{{%feedback_input_conditions_value_names}}',
            'common_language_id',
            '{{%common_languages}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //input conditions functionality
        $this->dropForeignKey('input_conditions_value_names-to-common_languages',
            '{{%feedback_input_conditions_value_names}}');
        $this->dropForeignKey('input_conditions_value_names-to-input_conditions_values',
            '{{%feedback_input_conditions_value_names}}');
        $this->dropTable('{{%feedback_input_conditions_value_names}}');

        $this->dropForeignKey('input_conditions-to-input_conditions_values',
            '{{%feedback_input_conditions}}');
        $this->dropForeignKey('input_conditions_values-to-input_conditions_templates',
            '{{%feedback_input_conditions_values}}');
        $this->dropTable('{{%feedback_input_conditions_values}}');

        $this->dropForeignKey('input_conditions-to-input_conditions_templates',
            '{{%feedback_input_conditions}}');
        $this->dropIndex('feedback_input_conditions-index', '{{%feedback_input_conditions}}');
        $this->dropTable('{{%feedback_input_conditions}}');

        $this->dropForeignKey('feedback_input_conditions_templates-to-common_languages',
            '{{%feedback_input_conditions_names}}');
        $this->dropForeignKey('input_conditions_names-to-input_conditions_templates',
            '{{%feedback_input_conditions_names}}');
        $this->dropTable('{{%feedback_input_conditions_names}}');

        $this->dropIndex('feedback_input_conditions_templates-index', '{{%feedback_input_conditions_templates}}');
        $this->dropTable('{{%feedback_input_conditions_templates}}');

        //input images functionality
        $this->dropForeignKey('input_images-to-input_images_templates',
            '{{%feedback_input_images}}');
        $this->dropIndex('feedback_input_images-index', '{{%feedback_input_images}}');
        $this->dropTable('{{%feedback_input_images}}');

        $this->dropForeignKey('feedback_input_image_names-to-common_languages',
            '{{%feedback_input_image_names}}');
        $this->dropForeignKey('input_image_names-to-input_images_templates',
            '{{%feedback_input_image_names}}');
        $this->dropTable('{{%feedback_input_image_names}}');

        $this->dropIndex('feedback_input_images_templates-index', '{{%feedback_input_images_templates}}');
        $this->dropTable('{{%feedback_input_images_templates}}');

        //input files functionality
        $this->dropForeignKey('feedback_input_files-to-feedback_input_files_templates',
            '{{%feedback_input_files}}');
        $this->dropIndex('input_file_reference-index', '{{%feedback_input_files}}');
        $this->dropTable('{{%feedback_input_files}}');

        $this->dropForeignKey('feedback_input_file_names-to-common_languages',
            '{{%feedback_input_file_names}}');
        $this->dropForeignKey('feedback_input_file_names-to-feedback_input_files_templates',
            '{{%feedback_input_file_names}}');
        $this->dropTable('{{%feedback_input_file_names}}');

        $this->dropIndex('input_file_template_reference-index', '{{%feedback_input_files_templates}}');
        $this->dropTable('{{%feedback_input_files_templates}}');

        //input fields functionality
        $this->dropForeignKey('input_fields_represents-to-input_fields_templates',
            '{{%feedback_input_fields_represents}}');
        $this->dropIndex('input_field_reference-index', '{{%feedback_input_fields_represents}}');
        $this->dropTable('{{%feedback_input_fields_represents}}');

        $this->dropForeignKey('feedback_input_field_templates_names-to-common_languages',
            '{{%feedback_input_field_templates_names}}');
        $this->dropForeignKey('input_field_templates_names-to-input_fields_templates',
            '{{%feedback_input_field_templates_names}}');
        $this->dropTable('{{%feedback_input_field_templates_names}}');

        $this->dropIndex('input_field_template_reference-index', '{{%feedback_input_fields_templates}}');
        $this->dropTable('{{%feedback_input_fields_templates}}');

        //feedback functionality
        $this->dropForeignKey('feedback_states-to-feedback',
            '{{%feedback_states}}');
        $this->dropTable('{{%feedback_states}}');

        $this->dropForeignKey('feedback_names_translates-to-common_languages',
            '{{%feedback_names_translates}}');
        $this->dropForeignKey('feedback_names_translates-to-feedback',
            '{{%feedback_names_translates}}');
        $this->dropTable('{{%feedback_names_translates}}');

        $this->dropTable('{{%feedback_config}}');

        $this->dropTable('{{%feedback}}');
    }
}
