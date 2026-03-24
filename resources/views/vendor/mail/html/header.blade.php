@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if(!empty($siteSettings['site_logo']))
<img src="{{ asset(Storage::url($siteSettings['site_logo'])) }}"
     class="logo"
     alt="{{ $siteSettings['site_name'] ?? config('app.name') }}"
     style="max-height:48px;width:auto;object-fit:contain;">
@else
{{ $siteSettings['site_name'] ?? $slot }}
@endif
</a>
</td>
</tr>
