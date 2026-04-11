Route::put('/admin/users/{id}', function (Illuminate\Http\Request $request, $id) {
    $user = \App\Models\User::findOrFail($id);
    $data = $request->validate([
        'timezone' => 'nullable|string',
    ]);
    if (isset($data['timezone'])) {
        $user->timezone = $data['timezone'];
    }
    $user->save();
    return response()->json(['success' => true, 'data' => $user]);
});

Route::delete('/admin/users/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    $user->delete();

    return response()->json(['status' => 'ok']);
});
