<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChapterEntry\ManageResource;
use App\Models\Chapter;
use App\Models\ChapterEntry;
use App\Models\CustomUser;
use App\Services\StringBladeService;
use App\View\Components\Blocks\RoleplayableSelector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChapterEntryController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param  Chapter  $chapter
     * @param  StringBladeService  $stringBlade
     * @return Response
     */
    public function create(Chapter $chapter, StringBladeService $stringBlade): Response
    {
        $entry = new ChapterEntry();
        $entry->setRelation('chapter', $chapter);
        $this->authorize('create', $entry);

        $blade = '<x-chapter-entry.create-chapter-entry :chapter="$chapter" />';

        $html = $stringBlade->render(
            $blade, compact('chapter')
        );

        return response($html);
    }

    /**
     * @param Chapter $chapter
     * @param StringBladeService $stringBlade
     * @return Response
     */
    public function createButton(Chapter $chapter, StringBladeService $stringBlade): Response
    {
        $entry = new ChapterEntry();
        $entry->setRelation('chapter', $chapter);
        $entry->chapter_id = $chapter->getKey();
        $this->authorize('create', $entry);

        $blade = '<x-chapter-entry.create-button :chapter="$chapter" />';

        $html = $stringBlade->render(
            $blade, compact('chapter')
        );

        return response($html);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ManageResource  $request
     * @param  Chapter  $chapter
     * @return RedirectResponse
     */
    public function store(ManageResource $request, Chapter $chapter): RedirectResponse
    {
        $entry = new ChapterEntry();

        // D??finir la relation.
        $entry->setRelation('chapter', $chapter);
        $entry->chapter_id = $chapter->getKey();

        // V??rifier que le chapitre est actif.
        if(! $entry->chapter->isCurrent()) {
            throw ValidationException::withMessages(["Ce chapitre n'est plus actif."]);
        }

        $this->authorize('create', $entry);

        // D??finir le roleplayable associ??.
        $roleplayable = RoleplayableSelector::createRoleplayableFromForm($request);
        /** @var CustomUser $user */
        $user = auth()->user();
        if(Gate::denies('manage', $chapter->roleplay) && ! $user->hasRoleplayable($roleplayable)) {
            throw ValidationException::withMessages(["Vous ne pouvez pas cr??er un post avec cette entit??."]);
        }
        $entry->roleplayable_id = $roleplayable->getKey();
        $entry->roleplayable_type = get_class($roleplayable);

        // R??cup??rer le titre et le contenu.
        $entry->fill($request->only($entry->getFillable()));

        // G??rer l'int??gration d'un m??dia.
        $request->setMediaFromRequest($entry);

        $entry->save();

        return redirect(route('roleplay.show', $entry->chapter->roleplay)
                . '#chapter-' . $entry->chapter->identifier)
            ->with('message', 'success|Ev??nement ajout?? avec succ??s.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ChapterEntry  $entry
     * @return Response
     */
    public function edit(ChapterEntry $entry): Response
    {
        return response()->noContent();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  ChapterEntry  $entry
     * @return Response
     */
    public function update(Request $request, ChapterEntry $entry): Response
    {
        return response()->noContent();
    }

    /**
     * Affiche le formulaire de suppression d'une entr??e de chapitre.
     *
     * @param  ChapterEntry  $entry
     * @return View
     */
    public function delete(ChapterEntry $entry): View
    {
        $this->authorize('manage', $entry);

        return view('chapter-entry.delete')->with('entry', $entry);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ChapterEntry  $entry
     * @return RedirectResponse
     */
    public function destroy(ChapterEntry $entry): RedirectResponse
    {
        $this->authorize('manage', $entry);

        $chapter = $entry->chapter;
        $roleplay = $chapter->roleplay;

        $entry->delete();

        return redirect(route('roleplay.show', $roleplay) . '#chapter-' . $chapter->identifier)
            ->with('message', 'success|Entr??e supprim??e avec succ??s.');
    }
}
