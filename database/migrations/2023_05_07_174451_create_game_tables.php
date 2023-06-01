<?php

use App\Models\InternalCommand;
use App\Models\InternalFunction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('header_value')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('combats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gender_id')
                ->constrained();
            $table->unsignedTinyInteger('number');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_description')
                ->nullable();
            $table->unsignedBigInteger('short_description_room_id') // Use short description of another room
                ->nullable();
            $table->foreign('short_description_room_id')
                ->references('id')
                ->on('rooms')
                ->constrained();
            $table->unsignedBigInteger('drop_move_room_id') // Objects dropped in this room are moved to another
                ->nullable();
            $table->foreign('drop_move_room_id')
                ->references('id')
                ->on('rooms')
                ->constrained();
            $table->text('long_description')
                ->nullable();
            $table->string('chain')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('attribute_room', function (Blueprint $table) {
            $table->foreignId('room_id')
                ->constrained();
            $table->foreignId('attribute_id')
                ->constrained();
            $table->timestamps();
        });

        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->text('pictorial_representation');
            $table->timestamps();
        });

        Schema::create('vocabulary_words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->morphs('word');
            $table->timestamps();
        });

        Schema::create('object_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('synonyms', function (Blueprint $table) {
            $table->id();
            $table->string('target');
            $table->timestamps();
        });

        Schema::create('object_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('object_class_id')
                ->constrained();
            $table->unsignedMediumInteger('weight')
                ->nullable();
            $table->mediumInteger('value')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('motions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('noises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('internal_commands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('internal_functions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('number_of_parameters')
                ->default(0);
            $table->timestamps();
        });

        Schema::create('verbs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('texts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('demons', function (Blueprint $table) {
            $table->id();
            $table->mediumInteger('number'); // not unsigned as can be zero
            $table->string('name');
            $table->string('system_attribute_1')
                ->nullable();
            $table->string('system_attribute_2')
                ->nullable();
            $table->unsignedMediumInteger('delay_minimum')
                ->nullable();
            $table->unsignedMediumInteger('delay_maximum')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('attribute_demon', function (Blueprint $table) {
            $table->foreignId('demon_id')
                ->constrained();
            $table->foreignId('attribute_id')
                ->constrained();
            $table->timestamps();
        });

        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('order')
                ->default(0);
            $table->morphs('type'); // verb or demon
            $table->foreignId('internal_command_id')
                ->nullable()
                ->constrained();
            $table->foreignId('internal_function_id')
                ->nullable()
                ->constrained();
            $table->unsignedBigInteger('to_object_class_id')
                ->nullable();
            $table->foreign('to_object_class_id')
                ->references('id')
                ->on('object_classes')
                ->constrained();
            $table->unsignedBigInteger('with_object_class_id')
                ->nullable();
            $table->foreign('with_object_class_id')
                ->references('id')
                ->on('object_classes')
                ->constrained();
            $table->unsignedBigInteger('function_object_form_id')
                ->nullable();
            $table->foreign('function_object_form_id')
                ->references('id')
                ->on('object_forms')
                ->constrained();
            $table->unsignedBigInteger('function_room_id')
                ->nullable();
            $table->foreign('function_room_id')
                ->references('id')
                ->on('rooms')
                ->constrained();
            $table->unsignedMediumInteger('function_value')
                ->nullable();
            $table->unsignedBigInteger('player_text_id')
                ->nullable();
            $table->foreign('player_text_id')
                ->references('id')
                ->on('texts')
                ->constrained();
            $table->unsignedBigInteger('local_text_id')
                ->nullable();
            $table->foreign('local_text_id')
                ->references('id')
                ->on('texts')
                ->constrained();
            $table->unsignedBigInteger('global_text_id')
                ->nullable();
            $table->foreign('global_text_id')
                ->references('id')
                ->on('texts')
                ->constrained();
            $table->foreignId('demon_id')
                ->nullable()
                ->constrained();
            $table->timestamps();
        });

        Schema::create('object_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_form_id')
                ->constrained();
            $table->smallInteger('version');
            $table->timestamps();
        });

        Schema::create('object_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_version_id')
                ->constrained();
            $table->smallInteger('number');
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('object_imprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_version_id')
                ->constrained();
            $table->unsignedsmallInteger('speed')
                ->nullable();
            $table->foreignId('demon_id')
                ->nullable()
                ->constrained();
            $table->unsignedTinyInteger('attack_probability')
                ->nullable();
            $table->unsignedSmallInteger('score')
                ->nullable();
            $table->unsignedSmallInteger('stamina')
                ->nullable();
            $table->boolean('is_light_source')
                ->default(false);
            $table->boolean('is_getable')
                ->default(false);
            $table->boolean('is_it')
                ->default(false);
            $table->unsignedMediumInteger('can_carry_weight')
                ->nullable();
            $table->boolean('is_disguised_container')
                ->default(false);
            $table->boolean('is_always_open_container')
                ->default(false);
            $table->boolean('is_transparent_container')
                ->default(false);
            $table->boolean('is_no_summon')
                ->default(false);
            $table->boolean('is_fixed')
                ->default(false);
            $table->unsignedSmallInteger('maximum_state_number')
                ->nullable();
            $table->timestamps();
        });

        Schema::create('object_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_imprint_id')
                ->constrained();
            $table->foreignId('room_id')
                ->nullable()
                ->constrained();
            $table->foreignId('object_state_id')
                ->nullable()
                ->constrained();
            $table->timestamps();
        });

        // Travel has no plural form in Laravel
        Schema::create('travel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')
                ->constrained();
            $table->unsignedTinyInteger('order');
            $table->nullableMorphs('destination');
            $table->nullableMorphs('condition');
            $table->boolean('condition_only_if_has_object')
                ->default(false);
            $table->boolean('if_empty')
                ->default(false);
            $table->boolean('is_game_over')
                ->default(false);
            $table->boolean('is_fixed_direction')
                ->default(false);
            $table->boolean('if_forced')
                ->default(false);
            $table->timestamps();
        });

        Schema::create('motion_travel', function (Blueprint $table) {
            $table->foreignId('motion_id')
                ->constrained();
            $table->foreignId('travel_id')
                ->constrained();
            $table->timestamps();
        });

        $this->insertInternalFunctions();
        $this->insertInternalCommands();
    }

    private function insertInternalFunctions(): void
    {
        $internalFunctionNames = [
            'backrot', 'create', 'dead', 'dec', 'decdestroy',
            'decifzero', 'decinc', 'delaymove', 'destroy', 'destroycreate',
            'destroydec', 'destroydestroy', 'destroyinc', 'destroyset', 'destroytogglesex',
            'destroytrans', 'disenable', 'emotion', 'enable', 'exp',
            'expdestroy', 'expinc', 'expmove', 'expset', 'fix',
            'flipat', 'float', 'floatdestroy', 'flush', 'forrot',
            'holdfirst', 'holdlast', 'hurt', 'ifasleep', 'ifberserk',
            'ifblind', 'ifdead', 'ifdeaf', 'ifdestroyed', 'ifdisenable',
            'ifdumb', 'ifenabled', 'iffighting', 'ifgot', 'ifhave',
            'ifhere', 'ifheretrans', 'ifill', 'ifin', 'ifinc',
            'ifinsis', 'ifinvis', 'iflevel', 'iflight', 'ifobjcontains',
            'ifobjcount', 'ifobjis', 'ifobjplayer', 'ifparalysed', 'ifplaying',
            'ifprop', 'ifpropdec', 'ifpropdestroy', 'ifpropinc', 'ifr',
            'ifrlevel', 'ifrprop', 'ifrstas', 'ifself', 'ifsex',
            'ifsmall', 'ifsnooping', 'ifweighs', 'ifwiz', 'ifzero',
            'inc', 'incdec', 'incdestroy', 'incmove', 'incsend',
            'injure', 'loseexp', 'losestamina', 'move', 'noifr',
            'null', 'resetdest', 'retal', 'send', 'sendeffect',
            'sendemon', 'sendlevel', 'sendmess', 'set', 'setdestroy',
            'setfloat', 'setsex', 'ssendemon', 'stamina', 'staminadestroy',
            'suspend', 'swap', 'testsex', 'testsmall', 'toggle',
            'togglesex', 'trans', 'transhere', 'transwhere', 'unlessberserk',
            'unlessdead', 'unlessdestroyed', 'unlessdisenable', 'unlessenabled', 'unlessfighting',
            'unlessgot', 'unlesshave', 'unlesshere', 'unlessill', 'unlessin',
            'unlessinc', 'unlessinsis', 'unlesslevel', 'unlessobjcontains', 'unlessobjis',
            'unlessobjplayer', 'unlessplaying', 'unlessprop', 'unlesspropdestroy', 'unlessrlevel',
            'unlessrstas', 'unlesssmall', 'unlesssnooping', 'unlessweighs', 'unlesswiz',
            'writein', 'zonk',
        ];

        foreach ($internalFunctionNames as $internalFunctionName) {
            InternalFunction::create([
                'name'  => $internalFunctionName
            ]);
        }
    }

    private function insertInternalCommands(): void
    {
        $internalCommandNames = [
           '.assist', '.attach', '.autowho', '.back', '.begone',
           '.berserk', '.blind', '.brief', '.bug', '.bye',
           '.change', '.converse', '.crash', '.ctrap', '.cure',
           '.deafen', '.debug', '.demo', '.detach', '.diagnose',
           '.direct', '.drop', '.dumb', '.eat', '.empty',
           '.enchant', '.exits', '.exorcise', '.flee', '.flush',
           '.fod', '.follow', '.freeze', '.get', '.go',
           '.haste', '.hours', '.humble', '.ignore', '.insert',
           '.inven', '.invis', '.keep', '.kill', '.laugh',
           '.log', '.look', '.lose', '.make', '.map',
           '.mobile', '.newhours', '.p', '.paralyse', '.password',
           '.peace', '.police', '.pronouns', '.proof', '.provoke',
           '.purge', '.quickwho', '.quit', '.refuse', '.remove',
           '.reset', '.resurrect', '.rooms', '.save', '.say',
           '.score', '.set', '.sget', '.sgo', '.shelve',
           '.sleep', '.snoop', '.spectacular', '.stamina', '.summon',
           '.tell', '.time', '.unfreeze', '.unkeep', '.unshelve',
           '.unsnoop', '.unveil', '.value', '.verbose', '.vis',
           '.wake', '.war', '.weigh', '.where', '.who',
        ];
        foreach ($internalCommandNames as $internalCommandName) {
            InternalCommand::create([
                'name'  => $internalCommandName
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_tables');
    }
};
