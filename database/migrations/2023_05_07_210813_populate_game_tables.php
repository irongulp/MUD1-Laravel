<?php

use App\Models\Action;
use App\Models\InternalCommand;
use App\Models\InternalFunction;
use App\Models\Verb;
use App\Models\Demon;
use App\Models\ObjectInstance;
use App\Models\Motion;
use App\Models\Noise;
use App\Models\ObjectClass;
use App\Models\ObjectForm;
use App\Models\ObjectState;
use App\Models\ObjectVersion;
use App\Models\Room;
use App\Models\Attribute;
use App\Models\Section;
use App\Models\Text;
use App\Models\Travel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use const http\Client\Curl\VERSIONS;

return new class extends Migration
{
    private const SOURCE_DIRECTORY = 'source';
    private const SOURCE_FILENAME =  self::SOURCE_DIRECTORY . '/' . 'MUD.TXT';
    private const SOURCE_EXTENSION = '.GET';
    private const COMMENT = ';';
    private const INCLUDE_FILE = '@';
    private const TAB = "\t";
    private const NUL = "\0";
    private const SECTION_START = '*';
    private const REPEAT = '%';
    private const USE_OTHER_ROOM_SHORT_DESCRIPTION = self::REPEAT;
    private const RANGE = '-';
    private const NOT = '~';
    private const DEMON_START = '$';
    private const COMMAND_START = '.';
    private const DEATH_ATTRIBUTE = 'death';
    private const NEW_LINE_AFTER_SHORT_LINE_LENGTH = 60;
    private const NO_CLASS = 'none';
    private const NO_PARAMETER = 'null';
    private const FUNCTION_ON_WITH_OBJECT = 'second';
    private const FUNCTIONS_WITH_VALUES = [
        'ifprop',
        'ifweighs',
        'hurt',
        'retal',
        'iflevel',
        'unlessplaying',
        'sendlevel',
        'unlessrlevel',
        'ssendemon',
        'ifr',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $source = $this->getContentsFromFile();

        $sections = array();
        foreach ($source as $line) {
            if ($line[0] == self::SECTION_START) {
                $lineArray = $this->getTabArray(substr($line, 1));
                $sectionName = array_shift($lineArray);
                $headerValue = implode(self::TAB, $lineArray);
                if (empty($headerValue)) {
                    $headerValue = null;
                }
                Section::create([
                    'name'          => $sectionName,
                    'header_value'  => $headerValue,
                ]);
            } else {
                $sections[$sectionName][] = $line;
            }
        }
        $this->processText($sections['text']);
        $this->processDemons($sections['demons']);
        $this->processRooms($sections['rooms']);
        $this->processVocabulary($sections['vocabulary']);
        $this->processTravel($sections['travel']);
        $this->processObjects($sections['objects']);

    }

    public function  getContentsFromFile(
        ?string $file = null
    ): array {
        $source = array();
        if (is_null($file)) {
            $fileName = base_path(self::SOURCE_FILENAME);
        } else {
            $fileName = self::SOURCE_DIRECTORY . '/' . mb_strtoupper(substr($file, 1)) . self::SOURCE_EXTENSION;
        }
        $content = file($fileName);
        foreach ($content as $line) {
            $line = preg_replace('/\r\n/', '', $line);
            $line = str_replace(self::NUL, '', $line);
            if (!empty($line)) {
                $firstCharacter = $line[0];
                if ($firstCharacter != self::COMMENT) {
                    if ($firstCharacter == self::INCLUDE_FILE) {
                        $source = [...$source, ...$this->getContentsFromFile($line)];
                    } else {
                        $source[] = $line;
                    }
                }
            }
        }

        return $source;
    }

    private function processVocabulary(array $vocabulary) {
        /** @var ?Model $model */
        $model = null;
        foreach ($vocabulary as $line) {
            $attributes = $this->getTabArray($line);
            $heading = array_shift($attributes);
            if ($heading) {
                switch ($heading) {
                    case 'class':
                        $model = ObjectClass::class;
                        break;
                    case 'motion':
                        $model = Motion::class;
                        break;
                    case 'noise':
                        $model = Noise::class;
                        break;
                    case 'object':
                        $model = ObjectForm::class;
                        break;
                    case 'action':
                        $model = Verb::class;
                        break;
                    default:
                        $model = null;
                }
            }
            switch ($model) {
                case ObjectClass::class:
                case Motion::class:
                case Noise::class:
                    $model::updateOrCreate([
                        'name' => array_shift($attributes),
                    ]);
                    break;
                case ObjectForm::class:
                    $name = array_shift($attributes);
                    $objectClass = ObjectClass::updateOrCreate([
                        'name' => array_shift($attributes)
                    ]);
                    $weight = array_shift($attributes);
                    $value = array_shift($attributes);
                    $objectForm = ObjectForm::make([
                        'name'      => $name,
                        'weight'    => $weight,
                        'value'     => $value,
                    ]);
                    $objectForm->objectClass()->associate($objectClass)->save();
                    break;
                case Verb::class:
                    $this->processAction($attributes);
            }
        }
    }

    private function processAction(array $attributes): void
    {
        $actionWord = array_shift($attributes);
        $internalCommand = null;
        $functionObjectForm = null;
        $functionRoom = null;
        $order = null;
        $functionValue = null;
        if (str_starts_with($actionWord, self::DEMON_START)) {
            $model = Demon::where('name', $actionWord)->firstOrFail();
        } else {
            $model = Verb::where('name', $actionWord)->first();
            if (is_null($model)) {
                $model = Verb::create([
                    'name' => $actionWord,
                ]);
                $order = 0;
            }
        }
        if (is_null($order)) {
            $previousAction = Action
                ::where('type_type', $model::class)
                ->where('type_id', $model->id)
                ->orderBy('order', 'desc')
                ->first();
            if ($previousAction) {
                $order = $previousAction->order + 1;
            } else {
                $order = 0;
            }
        }

        $next = array_shift($attributes);

        if (str_starts_with($next, self::COMMAND_START)) {
            $internalCommand = InternalCommand::where('name', $next)->firstOrFail();
            $next = array_shift($attributes);
        }

        if ($next == self::NO_CLASS) {
            $toObjectClass = null;
        } else {
            $toObjectClass = ObjectClass::updateOrCreate([
                'name'  => $next,
            ]);
        }

        $next = array_shift($attributes);

        if ($next == self::NO_CLASS) {
            $withObjectClass = null;
        } else {
            $withObjectClass = ObjectClass::updateOrCreate([
                'name'  => $next,
            ]);
        }

        $next = array_shift($attributes);
        $internalFunction = InternalFunction::where('name', $next)->first();
        if ($internalFunction) {
            $parameters = array();
            do {
                $next = array_shift($attributes);
                if (!is_numeric($next)) {
                    $parameters[] = $next;
                }
            } while (!is_numeric($next));
            if ($parameters[0] != self::NO_PARAMETER) {
                if ($parameters[0] == self::FUNCTION_ON_WITH_OBJECT) {
                    $functionObjectForm = $withObjectClass; // todo Check it shouldn't be $toObject
                } else {
                    $functionObjectForm = ObjectForm::where('name', $parameters[0])->firstOrFail();
                }
            }
            if (isset($parameters[1])) {
                $functionRoom = Room::where('name', $parameters[1])->firstOrFail();
            }
            if (in_array($internalFunction->name, self::FUNCTIONS_WITH_VALUES)) {
                $functionValue = $next;
                $next = array_shift($attributes);
            }
        }

        $playerText = null;
        if ($next > 0) {
            $playerText = Text::findOrFail($next);
        }

        $localText = null;
        $local = array_shift($attributes);
        if ($local > 0) {
            $localText = Text::findOrfail($local);
        }

        $next = array_shift($attributes);
        $globalText = null;
        $demon = null;
        if (is_numeric($next)) {
            if ($next > 0) {
                $globalText = Text::findOrFail($next);
            } else {
                $demon = Demon::where('number', $next * -1)->firstOrFail();
            }
        }

        $action = Action::make([
            'order'             => $order,
            'function_value'    => $functionValue,
        ]);

        $action->type()->associate($model);

        if ($internalCommand) {
            $action->internalCommand()->associate($internalCommand);
        }

        if ($internalFunction) {
            $action->internalFunction()->associate($internalFunction);
        }

        if ($toObjectClass) {
            $action->toObjectClass()->associate($toObjectClass);
        }

        if ($withObjectClass) {
            $action->withObjectClass()->associate($withObjectClass);
        }

        if ($functionObjectForm) {
            $action->functionObjectForm()->associate($functionObjectForm);
        }

        if ($functionRoom) {
            $action->functionRoom()->associate($functionRoom);
        }

        $action->playerText()->associate($playerText);
        if ($localText) {
            $action->localText()->associate($localText);
        }
        if ($globalText) {
            $action->globalText()->associate($globalText);
        }
        if ($demon) {
            $action->demon()->associate($demon);
        }

        $action->save();

        // todo Add functions

    }

    private function processRooms(array $roomsSection) {
        $name = null;
        $shortDescription = null;
        $longDescription = null;
        $roomAttributes = array();
        $chain = null;
        $droppedObjectsMovedTo = array();
        foreach ($roomsSection as $line) {
            if ($line[0] != self::TAB) {
                // New room
                if ($name) {
                    $this->processLastRoom(
                        $name,
                        $shortDescription,
                        $longDescription,
                        $chain,
                        $roomAttributes
                    );
                }
                $shortDescription = null;
                $longDescription = null;
                $newRoom = $this->getNewRoom($line);
                $name = $newRoom['name'];
                $roomAttributes = $newRoom['roomAttributes'];
                $chain = $newRoom['chain'];
                $droppedObjectsMovedTo = [...$droppedObjectsMovedTo, ...$newRoom['droppedObjectsMovedTo']];
            } else {
                // Continue with this room
                $line = substr($line, 1);
                if ($shortDescription or in_array(self::DEATH_ATTRIBUTE, $roomAttributes)) {
                    $longDescription .= ' ' . $line;
                } else {
                    $shortDescription = $line;

                }
            }
        }

        $this->processLastRoom(
            $name,
            $shortDescription,
            $longDescription,
            $chain,
            $roomAttributes
        );

        foreach ($droppedObjectsMovedTo as $rooms) {
            $room = Room::where('name', $rooms[0])->firstOrFail();
            $otherRoom = Room::where('name', $rooms[1])->firstOrFail();
            $room->droppedObjectsMovedTo()->associate($otherRoom)->save();
        }
    }

    private function getNewRoom(string $line): array
    {
        $roomAttributes = $this->getTabArray($line);
        $name = array_shift($roomAttributes);
        $chain = null;
        $droppedObjectsMovedTo = array();
        foreach ($roomAttributes as $key => $attribute) {
            if ($attribute == 'dmove') {
                $droppedObjectsMovedTo[] = [
                    $name,
                    $roomAttributes[$key + 1],
                ];
                unset ($roomAttributes[$key], $roomAttributes[$key + 1]);
            }
        }
        foreach ($roomAttributes as $key => $attribute) {
            if ($attribute == 'chain') {
                $chain = $roomAttributes[$key + 1] . ' ' . $roomAttributes[$key + 2];
                unset ($roomAttributes[$key], $roomAttributes[$key + 1], $roomAttributes[$key + 2]);
            }
        }

        return compact(
            'name',
            'roomAttributes',
            'chain',
            'droppedObjectsMovedTo'
        );
    }

    private function processLastRoom(
        string $name,
        ?string $shortDescription,
        ?string $longDescription,
        ?string $chain,
        array $roomAttributes
    ): void {
        if ($name) {
            $room = Room::make([
                'name'              => $name,
                'short_description' => $shortDescription,
                'long_description'  => $longDescription,
                'chain'             => $chain,
            ]);
            if ($shortDescription and $shortDescription[0] == self::USE_OTHER_ROOM_SHORT_DESCRIPTION) {
                $otherRoom = Room::where('name', substr($shortDescription, 1))->firstOrFail();
                $room->short_description = null;
                $room->sameShortDescriptionAs()->associate($otherRoom);
            }
            $room->save();
            foreach ($roomAttributes as $attribute) {
                $roomAttribute = Attribute
                    ::where('name', $attribute)->first();
                if (is_null($roomAttribute)) {
                    $roomAttribute = Attribute::create([
                        'name'  => $attribute,
                        'type'  => $room::class,
                    ]);
                }
                $room->attributes()->attach($roomAttribute);
            }
        }
    }

    private function processDemons(array $demons): void
    {
        foreach ($demons as $line) {
            $attributes = $this->getTabArray($line);
            $number = array_shift($attributes);
            $name = array_shift($attributes);
            $systemAttribute1 = array_shift($attributes);
            $systemAttribute2 = array_shift($attributes);
            $delay = array_shift($attributes);
            $delayMinimum = $delay;
            $delayMaximum = null;
            if ($delay and !is_numeric($delay)) {
                $delay = explode(self::RANGE, $delay);
                $delayMinimum = $delay[0];
                $delayMaximum = $delay[1];
            }
            $demon = Demon::create([
                'number'                => $number,
                'name'                  => $name,
                'system_attribute_1'    => $systemAttribute1 == 'none' ? null : $systemAttribute1,
                'system_attribute_2'    => $systemAttribute2 == 'none' ? null : $systemAttribute2,
                'delay_minimum'         => $delayMinimum == -1 ? null : $delayMinimum,
                'delay_maximum'         => $delayMaximum,
            ]);

            foreach ($attributes as $name) {
                $attribute = Attribute
                    ::where('name', $name)
                    ->where('type', $demon::class)
                    ->first();
                if (is_null($attribute)) {
                    $attribute = Attribute::create([
                        'name'  => $name,
                        'type'  => $demon::class
                    ]);
                }
                $demon->attributes()->attach($attribute);
            }
        }
    }

    private function processText(array $content): void
    {
        $textId = null;
        $lastLineLength = null;
        foreach ($content as $line) {
            if ($line[0] != self::TAB) {
                if ($textId) {
                    $this->processLastText(
                        $textId,
                        $content
                    );
                }
                $newText = $this->getNewText($line);
                $textId = $newText['id'];
                $content = $newText['content'];
                $lastLineLength = strlen($line);
            } else {
                if (str_ends_with($content, '.') or $lastLineLength < self::NEW_LINE_AFTER_SHORT_LINE_LENGTH) {
                    $content .= PHP_EOL;
                } else {
                    $content .= ' ';
                }
                $content .= substr($line, 1);
                $lastLineLength = strlen($line);
            }
        }
        $this->processLastText(
            $textId,
            $content
        );
    }

    private function getNewText(string $line): array
    {
        $attributes = $this->getTabArray($line);
        $id = array_shift($attributes);
        $content = array_shift($attributes);

        return compact(
            'id',
            'content'
        );
    }

    private function processLastText(
        int $textId,
        string $content
    ): void
    {
        // UpdateOrCreate needed because of duplicate keys (with identical content, c.f. 809)
        Text::updateOrCreate(
            [
            'id'        => $textId
            ],
            [
            'content'   => $content,
            ]
        );
    }

    private function processTravel(array $travel): void
    {
        foreach ($travel as $line) {
            if ($line[0] != self::TAB) {
                $room = $this->getNewTravelRoom($line);
                $order = 0;
            }
            $this->processTravelRoom($room, $line, $order);
            $order++;
        }
    }

    private function getNewTravelRoom(string $line): Room
    {
        $travelAttributes = $this->getTabArray($line);
        $name = array_shift($travelAttributes);
        return Room::where('name', $name)->firstOrFail();
    }

    private function processTravelRoom(
        Room $room,
        string $line,
        int $order
    ): void
    {
        $travelAttributes = $this->getTabArray($line);
        array_shift($travelAttributes);
        $condition = array_shift($travelAttributes);
        $destinationType = null;
        $destinationId = null;
        $conditionType = null;
        $conditionId = null;
        $ifEmpty = false;
        $isGameOver = false;
        $onlyIfHas = false;
        $isFixedDirection = false;
        $ifForced = false;

        switch ($condition) {
            case 'n':
                // No condition
                break;
            case 'e':
                $ifEmpty = true;
                break;
            case 'dd':
                $isGameOver = true;
                break;
            case 'd':
                $isFixedDirection = true;
                break;
            case 'forced':
            case 'f':
                $ifForced = true;
                break;
            default:
                if (is_numeric($condition)) {
                    if ($condition < 0) {
                        $demon = Demon::where('number', $condition * -1)->firstOrFail();
                        $destinationType = $demon::class;
                        $destinationId = $demon->id;
                    } else {
                        $text = Text::findOrFail($condition);
                        $destinationType = $text::class;
                        $destinationId = $text->id;
                    }
                } else {
                    if (str_starts_with($condition, self::NOT)) {
                        $onlyIfHas = true;
                        $condition = substr($condition, 1);
                    }
                    $objectClass = ObjectClass::where('name', $condition)->first();
                    if ($objectClass) {
                        $conditionType = $objectClass::class;
                        $conditionId = $objectClass->id;
                    } else {
                        $objectForm = ObjectForm::where('name', $condition)->firstOrFail();
                        $conditionType = $objectForm::class;
                        $conditionId = $objectForm->id;
                    }
                }
        }

        if (is_null($destinationType)) {
            $destinationAttribute = array_shift($travelAttributes);
            if ($destinationAttribute) {
                if (is_numeric($destinationAttribute)) {
                    $destination = Text::findOrFail($destinationAttribute);
                } else {
                    $destination = Room::where('name', $destinationAttribute)->first();
                    if (is_null($destination)) {
                        // Needed to change cutting to cuttin
                        $destination = Room
                            ::where('name', substr($destinationAttribute, 0, -1))
                            ->firstOrFail();
                    }
                }
                $destinationType = $destination::class;
                $destinationId = $destination->id;
            }
        }

        $travel = Travel::make([
            'order'                         => $order,
            'destination_type'              => $destinationType,
            'destination_id'                => $destinationId,
            'if_empty'                      => $ifEmpty,
            'condition_type'                => $conditionType,
            'condition_id'                  => $conditionId,
            'condition_only_if_has_object'  => $onlyIfHas,
            'is_game_over'                  => $isGameOver,
            'is_fixed_direction'            => $isFixedDirection,
            'if_forced'                     => $ifForced,
        ]);

        $travel->from()->associate($room)->save();

        foreach ($travelAttributes as $motionName) {
            $motion = Motion::where('name', $motionName)->first();
            if (is_null($motion)) {
                $motion = Motion::create([
                    'name'  => $motionName
                ]);
            }
            $travel->motions()->attach($motion);
        }
    }

    private function processObjects(array $objects): void
    {
        $objectInstance = null;
        $description = null;
        foreach ($objects as $line) {
            $attributes = $this->getTabArray($line);
            $id = array_shift($attributes);
            if ($id and !is_numeric($id)) {
                if ($objectInstance) {
                    $this->processCurrentObject(
                        $description,
                        $objectInstance,
                        $latestVersion,
                        $objectName,
                        $roomList,
                        $currentState,
                        $startState
                    );
                    $description = null;
                }
                $objectName = $id;
                $newObject = $this->getNewObjectInstance($attributes);
                $objectInstance = $newObject['objectInstance'];
                $startState = $newObject['startState'];
                $roomList = $newObject['roomList'];
                $latestVersion = ObjectVersion
                    ::whereHas('objectForm', function (Builder $q) use($objectName) {
                        $q->where('name', $objectName);
                    })
                    ->orderBy('version', 'desc')
                    ->first();
            } else {
                $next = array_shift($attributes);
                if (is_numeric($id)) {
                    if (str_starts_with($next, self::REPEAT)) {
                        if (!$objectInstance->exists) {
                            // use latest version of object
                            $objectInstance->objectVersion()->associate($latestVersion)->save();
                            $this->processObjectInstanceRooms($objectInstance, $roomList);
                        }
                    } else {
                        if (!$objectInstance->exists) {
                            // create new version of object
                            $objectVersion = $this->getNewVersionOfObject($objectName, $latestVersion);
                            $objectInstance->objectVersion()->associate($objectVersion)->save();
                            $this->processObjectInstanceRooms($objectInstance, $roomList);
                        }
                        if ($description) {
                            $this->createState($objectInstance->objectVersion, $currentState, $description);
                            $description = null;
                        }
                        $currentState = $id;

                    }
                }

                if (!str_starts_with($next, self::REPEAT)) {
                    $description .= ' ' . $next;
                }
            }
        }
        $this->processCurrentObject(
            $description,
            $objectInstance,
            $latestVersion,
            $objectName,
            $roomList,
            $currentState,
            $startState
        );
    }

    private function getNewObjectInstance(
        array $attributes
    ): array {
        $preRoomAttributes = array();
        $i = 0;
        do {
            $next = array_shift($attributes);
            if (is_numeric($next)) {
                $preRoomAttributes[$i] = $next;
                $i++;
            }
        } while (is_numeric($next));

        $speed = $preRoomAttributes[0] ?? null;
        $demonNumber = $preRoomAttributes[1] ?? null;
        $attackProbability = $preRoomAttributes[2] ?? null;

        $roomList = [$next];

        $i = 1;
        do {
            $next = array_shift($attributes);
            if ($next and !is_numeric($next)) {
                $roomList[$i] = $next;
                $i++;
            }
        } while ($next and !is_numeric($next));

        $startState = $next;
        $maximumStateNumber = array_shift($attributes);;
        if ($maximumStateNumber < 0) {
            $maximumStateNumber = rand(0, $maximumStateNumber * -1);
        }
        $score = array_shift($attributes);
        $next = array_shift($attributes);

        $stamina = null;
        if (is_numeric($next)) {
            if (is_numeric($speed)) { // Only set stamina for mobiles
                $stamina = $next;
            } // todo Determine whether -1 stamina for potty has any significance
        } else {
            array_unshift($attributes, $next); // Not used, so put it back
        }

        $isLightSource = false;
        $isGetable = true;
        $isIt = true;
        $canCarryWeight = null;
        $isDisguisedContainer = false;
        $isAlwaysOpenContainer = false;
        $isTransparentContainer = false;
        $isNoSummon = false;
        $isFixed = false;

        $attribute = array_shift($attributes);
        if ($attribute) {
            do {
                switch ($attribute) {
                    case 'bright':
                        $isLightSource = true;
                        break;
                    case 'noget':
                        $isGetable = false;
                        break;
                    case 'noit':
                        $isIt = false;
                        break;
                    case 'contains':
                        $canCarryWeight = array_shift($attributes);
                        break;
                    case 'disguised':
                        $isDisguisedContainer = true;
                        break;
                    case 'opened':
                        $isAlwaysOpenContainer = true;
                        break;
                    case 'transparent':
                        $isTransparentContainer = true;
                        break;
                    case 'nosummon':
                        $isNoSummon = true;
                        break;
                    case 'fixed':
                        $isFixed = true;
                        break;
                }
                $attribute = array_shift($attributes);
            } while ($attribute);
        }

        $objectInstance = ObjectInstance::make([
            'speed' => $speed,
            'attack_probability' => $attackProbability,
            'score' => $score,
            'stamina' => $stamina,
            'is_light_source' => $isLightSource,
            'is_getable' => $isGetable,
            'is_it' => $isIt,
            'can_carry_weight' => $canCarryWeight,
            'is_disguised_container' => $isDisguisedContainer,
            'is_always_open_container' => $isAlwaysOpenContainer,
            'is_transparent_container' => $isTransparentContainer,
            'is_no_summon' => $isNoSummon,
            'is_fixed' => $isFixed,
            'maximum_state_number' => $maximumStateNumber,
        ]);

        if ($demonNumber) {
            $demon = Demon::where('number', $demonNumber)->firstOrFail();
            $objectInstance->demon()->associate($demon);
        }

        // Don't save $objectInstance yet, as we don't know the version

        return compact(
            'objectInstance',
            'startState',
            'roomList'
        );
    }

    private function processCurrentObject(
        ?string $description,
        ObjectInstance $objectInstance,
        ?ObjectVersion $latestVersion,
        string $objectName,
        array $roomList,
        int $currentState,
        ?int $startState
    ): void {
        if (!$objectInstance->exists) {
            if (is_null($latestVersion)) {
                $latestVersion = $this->getNewVersionOfObject($objectName, $latestVersion);
            }
            $objectInstance->objectVersion()->associate($latestVersion)->save();
            $this->processObjectInstanceRooms($objectInstance, $roomList);
        }

        if ($description) {
            $this->createState($objectInstance->objectVersion, $currentState, $description);
        }

        if ($startState) {
            $this->setStateOfObjectInstance($objectInstance, $startState);
        }
    }

    private function getNewVersionOfObject(
        string $objectName,
        ?ObjectVersion $latestVersion
    ): ObjectVersion {
        $objectForm = ObjectForm::where('name', $objectName)->firstOrFail();
        $objectVersion = ObjectVersion::make([
            'version'    => ($latestVersion?->version ?? -1) + 1,
        ]);
        $objectVersion->objectForm()->associate($objectForm)->save();

        return $objectVersion;
    }

    private function processObjectInstanceRooms(
        ObjectInstance $objectInstance,
        array $roomList
    ): void
    {
        foreach ($roomList as $roomName) {
            $room = Room::where('name', $roomName)->first();
            if ($room) {
                $objectInstance->rooms()->attach($room);
            } else {
                // todo Place object in container
            }
        }
    }

    private function setStateOfObjectInstance(
        ObjectInstance $objectInstance,
        int $startState
    ): void
    {
        $objectState = ObjectState
            ::whereHas('objectVersion', function (Builder $q) use ($objectInstance) {
                $q->where('id', $objectInstance->objectVersion->id);
            })
            ->where('number', $startState)
            ->first(); // Some objects have a start state, but no states, e.g. rstop

        if ($objectState) {
            $objectInstance->objectState()->associate($objectState)->save();
        }
    }

    private function createState(
        ObjectVersion $objectVersion,
        int $stateNumber,
        string $description
    ): void
    {
        $objectState = ObjectState::make([
            'number'        => $stateNumber,
            'description'   => $description,
        ]);

        $objectState->objectVersion()->associate($objectVersion)->save();
    }

    private function getTabArray(string $line): array
    {
        if (str_contains($line, self::TAB)) {
            $array = explode(self::TAB, $line);
        } else {
            return [$line];
        }

        // todo Handle angle brackets (random each time) and square brackets (random on game creation, fixed thereafter)
        $content = array();
        $random = false;
        $randomArray = array();
        foreach ($array as $key => $element) {
            if (!str_starts_with($element, self::COMMENT)) {
                if ($random) {
                    if (str_ends_with($element, '>') or str_ends_with($element, ']')) {
                        $random = false;
                        $randomArray[] = substr($element, 0, -1);
                        $content[] = $randomArray[array_rand($randomArray)];
                    } else {
                        $randomArray[] = $element;
                    }
                } else {
                    if (str_starts_with($element, '<') or str_starts_with($element, '[')) {
                        $random = true;
                        $randomArray[] = substr($element, 1);
                    } else {
                        if ($key == 0 or $element !== '') {
                            $content[] = $element;
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
