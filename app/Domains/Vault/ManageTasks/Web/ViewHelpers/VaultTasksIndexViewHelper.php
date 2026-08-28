<?php

namespace App\Domains\Vault\ManageTasks\Web\ViewHelpers;

use App\Helpers\DateHelper;
use App\Models\ContactTask;
use App\Models\User;
use App\Models\Vault;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VaultTasksIndexViewHelper
{
    /**
     * Tasks in three groups — Overdue, This week, Completed — as the design
     * presents them, rather than one list per contact.
     */
    public static function data(Vault $vault, User $user): array
    {
        $tasks = ContactTask::whereIn('contact_id', $vault->contacts()->select('id'))
            ->with('contact')
            ->orderBy('due_at', 'asc')
            ->get();

        $now = Carbon::now($user->timezone);
        $endOfWeek = $now->copy()->addWeek();

        $open = $tasks->where('completed', false);

        return [
            'groups' => [
                self::group(
                    trans('Overdue'),
                    'var(--pink)',
                    $open->filter(fn (ContactTask $task): bool => $task->due_at !== null && $task->due_at->isBefore($now)),
                    $user
                ),
                self::group(
                    trans('This week'),
                    'var(--ink)',
                    $open->filter(fn (ContactTask $task): bool => $task->due_at === null || ! $task->due_at->isBefore($now) && $task->due_at->isBefore($endOfWeek)),
                    $user
                ),
                self::group(
                    trans('Completed'),
                    'var(--muted)',
                    $tasks->where('completed', true)->sortByDesc('completed_at')->take(10),
                    $user
                ),
            ],
        ];
    }

    private static function group(string $title, string $color, Collection $tasks, User $user): array
    {
        return [
            'title' => $title,
            'color' => $color,
            'count' => $tasks->count(),
            'tasks' => $tasks->map(fn (ContactTask $task) => self::dto($task, $user))->values(),
        ];
    }

    private static function dto(ContactTask $task, User $user): array
    {
        $contact = $task->contact;

        return [
            'id' => $task->id,
            'label' => $task->label,
            'completed' => $task->completed,
            'due_at' => $task->due_at !== null ? [
                'formatted' => DateHelper::format($task->due_at, $user),
                'value' => $task->due_at->format('Y-m-d'),
                'is_late' => $task->due_at->isPast(),
            ] : null,
            'url' => [
                'toggle' => route('contact.task.toggle', [
                    'vault' => $contact->vault_id,
                    'contact' => $contact->id,
                    'task' => $task->id,
                ]),
            ],
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar' => $contact->avatar,
                'url' => [
                    'show' => route('contact.show', [
                        'vault' => $contact->vault_id,
                        'contact' => $contact->id,
                    ]),
                ],
            ],
        ];
    }
}
