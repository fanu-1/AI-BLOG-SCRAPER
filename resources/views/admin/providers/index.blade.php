@extends('layouts.admin')

@section('title', 'AI Providers')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">AI Providers</h1>
        <div class="muted">Add OpenAI, Gemini, or Claude once, then assign them to any source.</div>
    </div>
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Back</a>
</div>

<div class="panel" style="margin-bottom:20px;">
    <div class="grid-2">
        <div>
            <h2 style="margin-top:0;">Simple setup</h2>
            <p class="muted" style="margin-top:0;">Gemini is added exactly like the others: choose <strong>Gemini</strong>, paste API key, set model, and save.</p>
            <div class="card" style="margin-top:14px;">
                <strong>Gemini example</strong>
                <p class="muted">Driver: <code>gemini</code></p>
                <p class="muted">Model: <code>gemini-1.5-flash</code> or <code>gemini-1.5-pro</code></p>
                <p class="muted">Base URL: leave blank unless you use a custom endpoint.</p>
            </div>
        </div>
        <div>
            <form method="POST" action="{{ route('admin.providers.store') }}" class="form-grid">
                @csrf
                <label>Name <input name="name" required placeholder="Gemini Editorial"></label>
                <label>Driver
                    <select name="driver">
                        <option value="openai">OpenAI</option>
                        <option value="gemini">Gemini</option>
                        <option value="claude">Claude</option>
                    </select>
                </label>
                <label>API Key <input name="api_key" type="password" placeholder="Paste API key"></label>
                <label>Model <input name="model" required placeholder="gemini-1.5-flash"></label>
                <label>Base URL <input name="base_url" placeholder="Optional"></label>
                <label>Temperature <input type="number" step="0.01" name="temperature" value="0.70"></label>
                <label>Max tokens <input type="number" name="max_tokens" value="2000"></label>
                <label class="full-width">System prompt <textarea name="system_prompt" placeholder="Optional writing instructions"></textarea></label>
                <label><span>Active</span><input type="checkbox" name="is_active" value="1" checked></label>
                <div class="full-width"><button class="btn btn-primary" type="submit">Create Provider</button></div>
            </form>
        </div>
    </div>
</div>

<div class="table-box panel">
    <table>
        <thead>
            <tr><th>Name</th><th>Driver</th><th>Model</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($providers as $provider)
                <tr>
                    <td colspan="5">
                        <form id="provider-update-{{ $provider->id }}" method="POST" action="{{ route('admin.providers.update', $provider) }}" class="form-grid">
                            @csrf
                            @method('PATCH')
                            <label>Name <input name="name" value="{{ $provider->name }}"></label>
                            <label>Driver
                                <select name="driver">
                                    <option value="openai" @selected($provider->driver === 'openai')>OpenAI</option>
                                    <option value="gemini" @selected($provider->driver === 'gemini')>Gemini</option>
                                    <option value="claude" @selected($provider->driver === 'claude')>Claude</option>
                                </select>
                            </label>
                            <label>API Key <input name="api_key" type="password" placeholder="Leave blank to keep current"></label>
                            <label>Model <input name="model" value="{{ $provider->model }}"></label>
                            <label>Base URL <input name="base_url" value="{{ $provider->base_url }}"></label>
                            <label>Temperature <input type="number" step="0.01" name="temperature" value="{{ $provider->temperature }}"></label>
                            <label>Max tokens <input type="number" name="max_tokens" value="{{ $provider->max_tokens }}"></label>
                            <label class="full-width">System prompt <textarea name="system_prompt">{{ $provider->system_prompt }}</textarea></label>
                            <label><span>Active</span><input type="checkbox" name="is_active" value="1" @checked($provider->is_active)></label>
                        </form>
                        <div class="actions" style="margin-top:12px;">
                            <button class="btn btn-primary btn-small" type="submit" form="provider-update-{{ $provider->id }}">Save</button>
                            <form id="provider-delete-{{ $provider->id }}" method="POST" action="{{ route('admin.providers.destroy', $provider) }}" onsubmit="return confirm('Delete provider?')">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button class="btn btn-danger btn-small" type="submit" form="provider-delete-{{ $provider->id }}">Delete</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
