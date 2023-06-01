<?php

namespace App\Http\Views;

use App\Models\Demon;
use App\Models\ObjectImprint;
use App\Models\Room;
use App\Models\Travel;
use App\Models\Verb;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Tsuka\Bootstrap4;
use function route;

class DebugRoom extends Bootstrap4
{
    public function build(Room $room): DebugRoom
    {

        $this->startPage(self::class);
        $this->startGrid();
        $this->startGridRow();
        $this->addH2($room->short ?? $room->name);
        $this->endGridRow();
        $this->startGridRow();
        $this->addDiv($room->long_description);
        $this->endGridRow();
        $this->addObjects($room);
        $this->addHr();
        $this->addTravel($room);
        $this->addActions($room);
        $this->endGrid();


        return $this;
    }

    private function addObjects(Room $room): void
    {
        foreach ($room->objectInstances as $objectInstance) {
            $this->addHr();
            $this->startGridRow();
            $this->addDiv(
                $objectInstance->description ??
                    $objectInstance->objectImprint->objectVersion->objectForm->name
            );
            $this->endGridRow();
        }
    }

    private function addTravel(Room $room): void
    {
        foreach ($room->travel as $travel) {
            $to = $travel->to;
            if ($to instanceof Demon) {
                $this->startGridRow();
                $this->startGridCell(3);
                if ($to->action->functionRoom) {
                    $this->startLink(route('debug-room', $travel->to->action->functionRoom->id));
                    $this->addDiv($to->action->functionRoom->short);
                } else {
                    $this->addDiv($to->name);
                }
                if ($to->action->functionRoom) {
                    $this->endLink();
                }
                $this->endGridCell();
                $this->addMotions($travel);
                $this->endGridRow();
            } else {
                if ($travel->to?->short or $travel->condition?->content or $travel->to?->content) {

                    if ($travel->to?->short) {
                        $this->startGridRow();
                        $this->startGridCell(3);
                        $this->startLink(route('debug-room', $travel->to->id));
                    }
                    $string = null;
                    if ($travel->to?->short) {
                        $string = $travel->to->short;
                    } elseif ($travel->condition?->content) {
                        $string = $travel->condition->content;
                    } elseif ($travel->to?->content) {
                        $string = $travel->to->content;
                    }

                    $this->addDiv($string);
                    if ($travel->to?->short) {
                        $this->endLink();
                        $this->endGridCell();
                        $this->addMotions($travel);
                        $this->endGridRow();
                    }
                }
            }
        }
    }

    private function addMotions(Travel $travel): void
    {
        foreach ($travel->motions as $motion) {
            $this->addGridCell($motion->name, 1);
        }
    }

    /**
     * @throws Exception
     */
    private function addActions(Room $room)
    {
        foreach ($room->functionActions as $action) {
            $this->addHr();
            if ($action->type instanceof Verb) {
                $this->startGridRow();
                $this->addGridCell($action->type->name, 3);
                if ($action->toObjectClass) {
                    $this->addGridCell($action->toObjectClass->name, 1);
                }
                if ($action->withObjectClass) {
                    $this->addGridCell($action->withObjectClass->name, 1);
                }
                if ($action->internalFunction) {
                    $this->addGridCell($action->internalFunction->name, 1);
                    if ($action->function_value) {
                        $this->addGridCell($action->function_value, 1);
                    }
                    if ($action->functionRoom) {
                        $this->addGridCell($action->functionRoom->short, 3);
                    }
                    if ($action->functionObjectForm) {
                        $this->addGridCell($action->functionObjectForm->name, 1);
                    }
                    if ($action->internalCommand) {
                        $this->addGridCell($action->internalCommand->name, 1);
                    }
                }

                if ($action->playerText) {
                    $this->addGridCell($action->playerText->content, 3);
                }
                $this->endGridRow();
                if ($action->internalFunction->is_transport and $action->withObjectClass) {
                    foreach ($action->withObjectClass->getObjectInstances() as $objectInstance) {
                        foreach ($objectInstance->rooms as $room) {
                            $this->startGridRow();
                            $this->startGridCell();
                            $this->addSpan($action->type->name . ' object in ');
                            $this->startSpan();
                            $this->addLink(route('debug-room', $room->id), $room->short);
                            $this->endSpan();
                            $this->endGridCell();
                            $this->endGridRow();
                        }
                    }
                }
            }
        }
    }
}
