<!doctype html>
<html lang="id">
    @include('dashboard.partials.shell.head', [
        'backendUrl' => $backendUrl ?? rtrim(url('/'), '/'),
    ])
    @include('dashboard.partials.shell.body')
</html>
