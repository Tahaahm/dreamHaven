<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Static pages --}}
    <url>
        <loc>https://dreammulk.com</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://dreammulk.com/properties</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>https://dreammulk.com/agents</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://dreammulk.com/offices</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://dreammulk.com/contact</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

    {{-- Dynamic: Properties --}}
    @foreach($properties as $property)
    <url>
        <loc>https://dreammulk.com/properties/{{ $property->id }}</loc>
        <lastmod>{{ $property->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Dynamic: Agents --}}
    @foreach($agents as $agent)
    <url>
        <loc>https://dreammulk.com/agents/{{ $agent->id }}</loc>
        <lastmod>{{ $agent->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Dynamic: Offices --}}
    @foreach($offices as $office)
    <url>
        <loc>https://dreammulk.com/offices/{{ $office->id }}</loc>
        <lastmod>{{ $office->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

</urlset>