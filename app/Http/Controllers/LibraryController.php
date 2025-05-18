<?php

namespace App\Http\Controllers;

use App\Enums\LibraryRole;
use App\Http\Requests\AddLibraryUsersRequest;
use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\LibraryRequest;
use App\Http\Requests\RemoveLibraryUserRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\LibraryResource;
use App\Http\Resources\LibraryUserResource;
use App\Models\Book;
use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Library::class);

        return LibraryResource::collection($request->user()->libraries->sortByDesc('updated_at'));
    }

    public function store(LibraryRequest $request)
    {
        $this->authorize('create', Library::class);

        $library = Library::create($request->validated());

        $library->users()->attach($request->user(), [
            'role' => LibraryRole::OWNER,
        ]);

        return new LibraryResource($library);
    }

    public function show(Library $library)
    {
        $this->authorize('view', $library);

        return new LibraryResource($library);
    }

    public function update(LibraryRequest $request, Library $library)
    {
        $this->authorize('update', $library);

        $library->update($request->validated());

        return new LibraryResource($library);
    }

    public function destroy(Library $library)
    {
        $this->authorize('delete', $library);

        $library->delete();

        return response()->json();
    }

    public function addUsers(AddLibraryUsersRequest $request, Library $library)
    {
        $this->authorize('update', $library);

        foreach ($request->input('users') as $userData) {
            $user = User::where('email', $userData['email'])->first();
            if ($user) {
                $library->users()->syncWithoutDetaching([
                    $user->id => ['role' => $userData['role']],
                ]);
            }
        }

        return response()->json();
    }

    public function removeUser(RemoveLibraryUserRequest $request, Library $library)
    {
        // The policy checks are already handled in the form request

        $userId = $request->input('user_id');

        // Check if the user is actually in the library
        if ($library->users()->where('user_id', $userId)->exists()) {
            $library->users()->detach($userId);
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'User is not a member of this library'], 422);
    }

    public function getUsers(Library $library)
    {
        $this->authorize('view', $library);

        // Eager load the pivot data which contains the role
        $users = $library->users()->get();

        return LibraryUserResource::collection($users);
    }

    public function createBook(CreateBookRequest $request, Library $library)
    {
        $library->books()->create($request->validated());
    }

    public function getBooks(Library $library)
    {
        $this->authorize('view', $library);

        $books = $library->books()->get();

        return BookResource::collection($books);
    }
}
