<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authorize {{ $client->name }} — Monica</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; display: flex; justify-content: center; padding-top: 10vh; margin: 0; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.1); padding: 2rem; max-width: 26rem; width: 100%; }
        h1 { font-size: 1.15rem; margin: 0 0 1rem; }
        ul { padding-left: 1.25rem; color: #374151; }
        .actions { display: flex; gap: .75rem; margin-top: 1.5rem; }
        button { flex: 1; padding: .6rem 1rem; border-radius: 6px; border: 1px solid transparent; font-size: .95rem; cursor: pointer; }
        .approve { background: #111827; color: #fff; }
        .deny { background: #fff; color: #111827; border-color: #d1d5db; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ $client->name }} wants to access your Monica account</h1>
    <p>Signed in as <strong>{{ $user->email }}</strong></p>

    @if (count($scopes) > 0)
        <p>This application will be able to:</p>
        <ul>
            @foreach ($scopes as $scope)
                <li>{{ $scope->description }}</li>
            @endforeach
        </ul>
    @endif

    <div class="actions">
        <form method="post" action="{{ route('passport.authorizations.approve') }}" style="flex:1;display:flex;">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="approve">Authorize</button>
        </form>
        <form method="post" action="{{ route('passport.authorizations.deny') }}" style="flex:1;display:flex;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="deny">Deny</button>
        </form>
    </div>
</div>
</body>
</html>
