<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
<script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<title>Dream Mulk — Properties</title>
<style>
:root{
  --B:   #303b97;  /* brand blue */
  --BD:  #1a225a;  /* dark blue */
  --BL:  #eef0fb;  /* light blue tint */
  --G:   #d4af37;  /* gold */
  --GL:  #f5e9b0;  /* gold light */
  --ink: #0d1117;
  --mid: #52596e;
  --dim: #9aa0b8;
  --hr:  #e4e6f0;
  --bg:  #f4f6fa;
  --card:#ffffff;
  --sw:  300px; /* Slightly wider sidebar for breathing room */
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;outline:none;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}
img{display:block;}

/* ─── LAYOUT ─── */
.wrap{display:flex;min-height:100vh;padding-top:80px;}

/* ─── SIDEBAR ─── */
.sb{
  width:var(--sw);position:fixed;top:80px;left:0;bottom:0;
  background:#fff;border-right:1px solid var(--hr);
  z-index:50;overflow-y:auto;overflow-x:hidden;
  transition:transform .4s cubic-bezier(0.16, 1, 0.3, 1);
  scrollbar-width:none;
}
.sb::-webkit-scrollbar{display:none;}
.sb-line{height:4px;background:linear-gradient(90deg,var(--B),var(--G));}
.sb-hd{
  padding:28px 24px 20px;
  border-bottom:1px solid var(--hr);
  display:flex;align-items:center;gap:14px;
}
.sb-hd-icon{
  width:42px;height:42px;border-radius:12px;
  background:var(--BL);display:flex;align-items:center;justify-content:center;
  color:var(--B);font-size:16px;flex-shrink:0;
}
.sb-hd-title{font-size:17px;font-weight:700;color:var(--ink);}
.sb-hd-sub{font-size:12px;color:var(--dim);margin-top:2px;}
.sb-inner{padding:24px;}
.fg{margin-bottom:20px;}
.fg-label{
  display:block;font-size:11px;font-weight:700;
  text-transform:uppercase;letter-spacing:1.5px;
  color:var(--B);margin-bottom:8px;
}
.fc{
  width:100%;padding:12px 16px;
  font-family:'Outfit',sans-serif;font-size:14px;
  color:var(--ink);background:var(--bg);
  border:1px solid var(--hr);border-radius:12px;
  transition:all .3s ease;
  appearance:none;-webkit-appearance:none;
}
.fc:focus{border-color:var(--B);background:#fff;box-shadow:0 0 0 4px rgba(48,59,151,.08);}
.fc::placeholder{color:var(--dim);}
.sel-wrap{position:relative;}
.sel-wrap::after{
  content:'\f107';font-family:'Font Awesome 6 Free';font-weight:900;
  position:absolute;right:16px;top:50%;transform:translateY(-50%);
  color:var(--B);font-size:12px;pointer-events:none;
}
.sel-wrap select{padding-right:36px;cursor:pointer;}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.divider{height:1px;background:var(--hr);margin:24px 0;}

.btn-apply{
  width:100%;padding:14px;border-radius:12px;border:none;
  background:linear-gradient(135deg, var(--B), var(--BD));color:#fff;
  font-family:'Outfit',sans-serif;font-size:14px;font-weight:600;
  cursor:pointer;letter-spacing:.5px;
  display:flex;align-items:center;justify-content:center;gap:8px;
  transition:all .3s ease;margin-bottom:12px;
  box-shadow:0 6px 20px rgba(48,59,151,.2);
}
.btn-apply:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(48,59,151,.35);}
.btn-reset{
  width:100%;padding:13px;border-radius:12px;
  background:#fff;border:1px solid var(--hr);
  font-family:'Outfit',sans-serif;font-size:14px;font-weight:600;color:var(--mid);
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
  transition:all .3s ease;
}
.btn-reset:hover{border-color:var(--B);color:var(--B);background:var(--BL);}

/* ─── MAIN ─── */
.main{flex:1;margin-left:var(--sw);padding:40px 32px 80px;min-width:0;}

/* header */
.pg-head{
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:16px;margin-bottom:36px;
}
.pg-tag{
  font-size:11px;font-weight:700;letter-spacing:2px;
  text-transform:uppercase;color:var(--G);
  display:flex;align-items:center;gap:10px;margin-bottom:8px;
}
.pg-tag::before{content:'';width:30px;height:2px;background:var(--G);border-radius:2px;}
.pg-title{
  font-family:'Libre Baskerville',serif;
  font-size:clamp(28px,4vw,44px);font-weight:700;
  color:var(--BD);line-height:1.1;letter-spacing:-.5px;
}
.pg-title em{font-style:italic;color:var(--B);font-weight:400;}
.count-badge{
  display:flex;align-items:center;gap:10px;
  background:#fff;border:1px solid var(--hr);
  border-radius:50px;padding:10px 22px;
  font-size:14px;color:var(--mid);font-weight:500;
  box-shadow:0 4px 15px rgba(0,0,0,.03);
}
.count-badge strong{color:var(--B);font-weight:700;font-size:16px;}
.count-dot{width:10px;height:10px;border-radius:50%;background:var(--G);flex-shrink:0;box-shadow:0 0 0 3px var(--GL);}

/* ─── GRID ─── */
.grid{display:block;width:100%;min-height:400px;margin:0 -12px;}
.pc{
  width:33.333%;padding:0 12px;
  margin-bottom:28px;float:left;box-sizing:border-box;
}

/* ─── CARD (Now Clickable) ─── */
.card{
  background:var(--card);border-radius:20px;
  border:1px solid rgba(228, 230, 240, 0.8);overflow:hidden;
  display:flex;flex-direction:column;
  box-shadow:0 8px 24px rgba(13, 17, 39, 0.04);
  transition:all .4s cubic-bezier(.16,1,.3,1);
  position:relative;
  text-decoration:none; /* Ensure link has no underline */
  color:inherit;
  cursor:pointer;
  height: 100%;
}
.card:hover{
  transform:translateY(-10px);
  box-shadow:0 20px 40px rgba(48,59,151,.12);
  border-color:rgba(48,59,151,.15);
}

/* sliding bottom accent */
.card::after{
  content:'';position:absolute;
  bottom:0;left:0;right:0;height:4px;z-index:4;
  background:linear-gradient(90deg,var(--B),var(--G));
  transform:scaleX(0);transform-origin:left;
  transition:transform .5s cubic-bezier(.16,1,.3,1);
}
.card:hover::after{transform:scaleX(1);}

/* ── image ── */
.ci{position:relative;height:240px;overflow:hidden;flex-shrink:0;}
.ci-bg{
  position:absolute;inset:0;
  background-size:cover;background-position:center;
  transition:transform .8s cubic-bezier(.16,1,.3,1);
}
.card:hover .ci-bg{transform:scale(1.08);}
.ci::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to top,rgba(13,17,39,.9) 0%,rgba(13,17,39,.2) 40%,transparent 100%);
  z-index:1;
}

/* badges */
.ci-badges{
  position:absolute;top:16px;left:16px;right:16px;
  display:flex;justify-content:space-between;z-index:3;
}
.badge{
  font-size:10px;font-weight:700;letter-spacing:1.2px;
  text-transform:uppercase;padding:6px 14px;border-radius:30px;
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  font-family:'Outfit',sans-serif;
}
.badge-type{background:rgba(48,59,151,.85);color:#fff;border:1px solid rgba(255,255,255,.2);}
.badge-sell{background:rgba(255,255,255,.95);color:var(--BD);}
.badge-rent{background:var(--G);color:var(--BD);}

/* price overlay */
.ci-price{
  position:absolute;bottom:16px;left:16px;right:16px;z-index:3;
  display:flex;align-items:flex-end;justify-content:space-between;
}
.ci-price-main{
  font-family:'Libre Baskerville',serif;
  font-size:26px;font-weight:700;color:#fff;
  line-height:1;letter-spacing:-.5px;
  text-shadow:0 4px 15px rgba(0,0,0,.5);
}
.ci-price-sub{font-size:11px;font-weight:600;letter-spacing:1.5px;color:rgba(255,255,255,.7);margin-bottom:4px;text-transform:uppercase;}

/* ── body ── */
.cb{padding:20px 20px 0;flex:1;display:flex;flex-direction:column;}
.cb-title{
  font-family:'Libre Baskerville',serif;
  font-size:18px;font-weight:700;color:var(--ink);
  line-height:1.4;margin-bottom:8px;
  overflow:hidden;display:-webkit-box;
  -webkit-line-clamp:1;-webkit-box-orient:vertical;
  transition:color .3s;
}
.card:hover .cb-title{color:var(--B);}
.cb-loc{
  font-size:13px;color:var(--dim);font-weight:500;
  display:flex;align-items:center;gap:6px;margin-bottom:18px;
}
.cb-loc i{color:var(--B);font-size:12px;}

/* feature chips */
.cb-feats{
  display:flex;gap:8px;margin-top:auto;padding-bottom:18px;
}
.feat{
  flex:1;display:flex;flex-direction:column;
  align-items:center;gap:4px;padding:12px 6px;
  background:var(--bg);border-radius:12px;
  transition:all .3s ease;
}
.card:hover .feat{background:var(--BL);transform:translateY(-2px);}
.feat i{font-size:14px;color:var(--B);margin-bottom:2px;}
.feat-v{
  font-family:'Libre Baskerville',serif;
  font-size:16px;font-weight:700;color:var(--BD);line-height:1;
}
.feat-l{font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--dim);}

/* ── CTA ── */
.cta{padding:0 20px 20px;}
.cta-btn{
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 18px;border-radius:12px;
  background:#fff;border:1px solid var(--hr);
  font-size:14px;font-weight:600;color:var(--B);
  transition:all .3s ease;
}
.card:hover .cta-btn{background:var(--B);color:#fff;border-color:var(--B);box-shadow:0 8px 20px rgba(48,59,151,.25);}
.cta-arr{
  width:30px;height:30px;border-radius:8px;
  background:var(--BL);
  display:flex;align-items:center;justify-content:center;
  font-size:12px;transition:all .3s;
}
.card:hover .cta-arr{background:rgba(255,255,255,.2);transform:translateX(4px);}

/* ─── EMPTY ─── */
.empty{clear:both;width:100%;padding:80px 40px;text-align:center;background:#fff;border-radius:20px;border:2px dashed var(--hr);}
.empty-ic{width:70px;height:70px;border-radius:50%;background:var(--BL);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;color:var(--B);}
.empty h3{font-family:'Libre Baskerville',serif;font-size:22px;color:var(--BD);margin-bottom:8px;}
.empty p{font-size:14px;color:var(--dim);}

/* ─── PAGINATION ─── */
.pgn { clear: both; padding-top: 50px; display: flex; flex-direction: column; align-items: center; gap: 16px; }

/* Custom top info text */
.pgn-info { font-size: 14px; color: var(--mid); font-weight: 500; }
.pgn-info strong { color: var(--B); font-weight: 700; }

/* 1. Strip Laravel's extra blocks */
.pgn nav > div.sm\:hidden { display: none !important; }
.pgn nav > div > div:first-child { display: none !important; }

/* 2. Container holding the numbers */
.pgn nav > div > div:last-child > span,
.pgn nav ul.pagination {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  gap: 10px !important;
  box-shadow: none !important;
  margin: 0 !important;
  padding: 0 !important;
}

/* 3. Force Exact Square/Squircle Size on ALL buttons */
.pgn nav a.relative,
.pgn nav span[aria-current] > span,
.pgn nav span[aria-disabled] > span,
.pgn nav li > a,
.pgn nav li > span {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 44px !important;
  height: 44px !important;
  min-width: 44px !important; /* Fixes the squishing */
  padding: 0 !important; /* Overrides Laravel's default padding */
  margin: 0 !important; /* Overrides Laravel's negative margins */
  border-radius: 12px !important;
  background: #fff !important;
  border: 1.5px solid var(--hr) !important;
  color: var(--mid) !important;
  font-family: 'Outfit', sans-serif !important;
  font-size: 15px !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  box-shadow: 0 4px 10px rgba(13,17,39,0.03) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  z-index: 1 !important;
}

/* 4. Hover State */
.pgn nav a.relative:hover,
.pgn nav li > a:hover {
  border-color: var(--B) !important;
  color: var(--B) !important;
  background: var(--BL) !important;
  transform: translateY(-3px) !important;
  box-shadow: 0 8px 20px rgba(48,59,151,0.15) !important;
  z-index: 2 !important;
}

/* 5. Active State (Current Page) */
.pgn nav span[aria-current="page"] > span,
.pgn nav span.active,
.pgn nav li.active > span {
  background: linear-gradient(135deg, var(--B), var(--BD)) !important;
  color: #fff !important;
  border-color: transparent !important;
  box-shadow: 0 8px 24px rgba(48,59,151,0.3) !important;
  transform: scale(1.05) !important;
  z-index: 3 !important;
}

/* 6. Disabled State (Arrows at the end) */
.pgn nav span[aria-disabled="true"] > span,
.pgn nav span.disabled,
.pgn nav li.disabled > span {
  background: var(--bg) !important;
  color: var(--dim) !important;
  border-color: var(--hr) !important;
  box-shadow: none !important;
  cursor: not-allowed !important;
  transform: none !important;
}

/* Fix SVG arrow sizing */
.pgn nav svg { width: 18px !important; height: 18px !important; margin: 0 !important; display: block !important; }

/* ─── MOBILE ─── */
.mob-btn{
  display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
  padding:14px 28px;background:var(--BD);color:#fff;border-radius:50px;
  font-family:'Outfit',sans-serif;font-size:14px;font-weight:600;
  align-items:center;gap:10px;z-index:100;border:none;cursor:pointer;
  box-shadow:0 10px 35px rgba(26,34,90,.5);letter-spacing:.5px;
}
.mob-btn span{width:8px;height:8px;border-radius:50%;background:var(--G);display:block;box-shadow:0 0 0 2px rgba(212, 175, 55, 0.3);}
.sb-overlay{position:fixed;inset:0;background:rgba(13,17,39,.6);z-index:30;display:none;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);}

@media(max-width:1280px){.pc{width:50%;}}
@media(max-width:1024px){
  .sb{transform:translateX(-100%);}
  .sb.open{transform:translateX(0);box-shadow:0 0 80px rgba(0,0,0,.4);}
  .main{margin-left:0;padding:24px 20px 100px;}
  .mob-btn{display:flex;}
}
@media(max-width:600px){.pc{width:100%;padding:0;}}
</style>
</head>
<body>

@php $navbarStyle = 'navbar-light'; @endphp
@include('navbar')

<div class="wrap">

{{-- SIDEBAR --}}
<aside class="sb" id="sb">
  <div class="sb-line"></div>
  <div class="sb-hd">
    <div class="sb-hd-icon"><i class="fas fa-sliders-h"></i></div>
    <div>
      <div class="sb-hd-title">Filter</div>
      <div class="sb-hd-sub">Refine your search</div>
    </div>
  </div>
  <div class="sb-inner">

    <div class="fg">
      <label class="fg-label">Listing Type</label>
      <div class="sel-wrap"><select id="property-type-dropdown" class="fc">
        <option value="">Buy or Rent</option>
        <option value="sell">Buy</option>
        <option value="rent">Rent</option>
      </select></div>
    </div>

    <div class="fg">
      <label class="fg-label">City</label>
      <div class="sel-wrap"><select id="city-dropdown" class="fc"><option value="">Loading…</option></select></div>
    </div>

    <div class="fg">
      <label class="fg-label">Area</label>
      <div class="sel-wrap"><select id="area-dropdown" class="fc" disabled><option value="">Select city first</option></select></div>
    </div>

    <div class="fg">
      <label class="fg-label">Type</label>
      <div class="sel-wrap"><select id="purpose-dropdown" class="fc">
        <option value="">All Types</option>
        <option value="villa">Villa</option>
        <option value="house">House</option>
        <option value="apartment">Apartment</option>
        <option value="commercial">Commercial</option>
      </select></div>
    </div>

    <div class="fg">
      <label class="fg-label">Keywords</label>
      <input type="text" id="search-keywords-input" class="fc" placeholder="Pool, Garden…">
    </div>

    <div class="fg">
      <label class="fg-label">Price (USD)</label>
      <div class="two-col">
        <input type="number" id="min-price-input" class="fc" placeholder="Min">
        <input type="number" id="max-price-input" class="fc" placeholder="Max">
      </div>
    </div>

    <div class="divider"></div>
    <button class="btn-apply" id="search-button"><i class="fas fa-search"></i> Apply Filters</button>
    <button class="btn-reset" id="clear-filters"><i class="fas fa-rotate-left"></i> Reset Filters</button>
  </div>
</aside>

{{-- MAIN --}}
<main class="main">

  <div class="pg-head">
    <div class="pg-head-left">
      <div class="pg-tag">Kurdistan Real Estate</div>
      <div class="pg-title">Dream Mulk <em>Collection</em></div>
    </div>
    <div class="count-badge">
      <div class="count-dot"></div>
      <span><strong id="results-counter">{{ $properties->total() }}</strong> Properties</span>
    </div>
  </div>

  <div class="grid" id="propertiesGrid">
    @foreach($properties as $property)
    <div class="pc"
      data-type="{{ strtolower($property->type['category'] ?? '') }}"
      data-listing="{{ strtolower($property->listing_type ?? '') }}"
      data-price="{{ $property->price['usd'] ?? 0 }}"
      data-date="{{ $property->created_at->timestamp }}">

      {{-- ENTIRE CARD IS NOW AN ANCHOR TAG --}}
      <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}" class="card">

        <div class="ci">
          <div class="ci-bg" style="background-image:url('{{ !empty($property->images) ? $property->images[0] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=700&q=80' }}')"></div>
          <div class="ci-badges">
            <span class="badge badge-type">{{ $property->type['category'] ?? 'Property' }}</span>
            @php $lt = strtolower($property->listing_type ?? ''); @endphp
            <span class="badge {{ $lt === 'rent' ? 'badge-rent' : 'badge-sell' }}">{{ ucfirst($lt ?: 'N/A') }}</span>
          </div>
          <div class="ci-price">
            <div>
              <div class="ci-price-sub">Price</div>
              <div class="ci-price-main">${{ number_format($property->price['usd'] ?? 0) }}</div>
            </div>
          </div>
        </div>

        <div class="cb">
          <div class="cb-title">{{ $property->name['en'] ?? 'Exclusive Property' }}</div>
          <div class="cb-loc"><i class="fas fa-location-dot"></i>{{ $property->address ?? 'Kurdistan Region' }}</div>
          <div class="cb-feats">
            <div class="feat"><i class="fas fa-bed"></i><span class="feat-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span><span class="feat-l">Beds</span></div>
            <div class="feat"><i class="fas fa-bath"></i><span class="feat-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span><span class="feat-l">Baths</span></div>
            <div class="feat"><i class="fas fa-ruler-combined"></i><span class="feat-v">{{ $property->area ?? '—' }}</span><span class="feat-l">m²</span></div>
          </div>
        </div>

        <div class="cta">
          {{-- CTA IS NOW JUST A DIV TO AVOID NESTED <a> TAGS --}}
          <div class="cta-btn">
            <span>View Details</span>
            <span class="cta-arr"><i class="fas fa-arrow-right"></i></span>
          </div>
        </div>

      </a>
    </div>
    @endforeach

    @if($properties->count() === 0)
    <div class="empty">
      <div class="empty-ic"><i class="fas fa-search"></i></div>
      <h3>No Properties Found</h3>
      <p>Try adjusting your filters to find what you're looking for.</p>
    </div>
    @endif
  </div>

  <div class="pgn">
    <div class="pgn-info">
      Page <strong>{{ $properties->currentPage() }}</strong> of <strong>{{ $properties->lastPage() }}</strong>
      &nbsp;·&nbsp; <strong>{{ $properties->total() }}</strong> total listings
    </div>
    {{ $properties->links() }}
  </div>

</main>

<button class="mob-btn" id="mobBtn"><span></span><i class="fas fa-sliders-h"></i> Filters</button>
<div class="sb-overlay" id="sbOverlay"></div>
</div>

<script>
class LocationSelector{
  constructor(o={}){
    this.cId=o.citySelectId||'c';this.aId=o.areaSelectId||'a';
    this.onC=o.onCityChange||null;this.onA=o.onAreaChange||null;
    this.cities=[];this.curC=o.selectedCityId||null;this.curA=o.selectedAreaId||null;
  }
  async init(){
    try{await this.loadCities();this.bind();if(this.curC)await this.loadAreas(this.curC);}catch(e){console.error(e);}
  }
  async loadCities(){
    const el=document.getElementById(this.cId);
    try{
      const r=await fetch('/v1/api/location/branches',{headers:{'Accept-Language':'en'}});
      const d=await r.json();
      if(d.success&&Array.isArray(d.data)){this.cities=d.data;this.fillCities();}
    }catch(e){if(el)el.innerHTML='<option value="">All Cities</option>';}
  }
  fillCities(){
    const el=document.getElementById(this.cId);if(!el)return;
    el.innerHTML='<option value="">All Cities</option>';
    [...this.cities].sort((a,b)=>a.city_name_en.localeCompare(b.city_name_en)).forEach(c=>{
      const o=document.createElement('option');
      o.value=c.id;o.textContent=c.city_name_en;o.dataset.nameEn=c.city_name_en;
      if(c.id==this.curC)o.selected=true;el.appendChild(o);
    });
  }
  async loadAreas(id){
    const el=document.getElementById(this.aId);if(!el)return;
    el.innerHTML='<option value="">Loading…</option>';el.disabled=true;
    try{
      const r=await fetch(`/v1/api/location/branches/${id}/areas`,{headers:{'Accept-Language':'en'}});
      const d=await r.json();if(d.success&&d.data)this.fillAreas(d.data);
    }catch(e){}finally{el.disabled=false;}
  }
  fillAreas(areas){
    const el=document.getElementById(this.aId);if(!el)return;
    el.innerHTML='<option value="">All Areas</option>';
    [...areas].sort((a,b)=>a.area_name_en.localeCompare(b.area_name_en)).forEach(a=>{
      const o=document.createElement('option');
      o.value=a.id;o.textContent=a.area_name_en;o.dataset.nameEn=a.area_name_en;
      if(a.id==this.curA)o.selected=true;el.appendChild(o);
    });
  }
  bind(){
    const c=document.getElementById(this.cId),a=document.getElementById(this.aId);
    if(c)c.addEventListener('change',async e=>{
      if(e.target.value){await this.loadAreas(e.target.value);if(this.onC)this.onC(e.target.value);}
      else{if(a){a.innerHTML='<option value="">Select City First</option>';a.disabled=true;}if(this.onC)this.onC(null);}
    });
    if(a)a.addEventListener('change',e=>{if(this.onA)this.onA(e.target.value);});
  }
}
</script>

<script>
$(function(){
  var $g=$('#propertiesGrid'),ok=false;
  $g.imagesLoaded(function(){
    $g.isotope({itemSelector:'.pc',percentPosition:true,layoutMode:'fitRows'});ok=true;
  });
  function go(){
    if(!ok)return;
    var kw=$('#search-keywords-input').val().toLowerCase();
    var mn=parseFloat($('#min-price-input').val())||0;
    var mx=parseFloat($('#max-price-input').val())||Infinity;
    var tp=$('#purpose-dropdown').val().toLowerCase();
    var ls=$('#property-type-dropdown').val().toLowerCase();
    var cy=($('#city-dropdown option:selected').data('nameEn')||'').toLowerCase();
    var ar=($('#area-dropdown option:selected').data('nameEn')||'').toLowerCase();
    $g.isotope({filter:function(){
      var $t=$(this),tx=$t.text().toLowerCase(),p=parseFloat($t.attr('data-price'));
      return(!kw||tx.includes(kw))&&p>=mn&&p<=mx
        &&(!tp||$t.attr('data-type')===tp)&&(!ls||$t.attr('data-listing')===ls)
        &&(!cy||tx.includes(cy))&&(!ar||tx.includes(ar));
    }});
    setTimeout(()=>$('#results-counter').text($g.data('isotope').filteredItems.length),120);
  }
  new LocationSelector({citySelectId:'city-dropdown',areaSelectId:'area-dropdown',onCityChange:go,onAreaChange:go}).init();
  $('#search-button').on('click',go);
  $('#clear-filters').on('click',function(){
    $('input.fc').val('');$('select.fc').val('');
    if(ok)$g.isotope({filter:'*'});
    setTimeout(()=>$('#results-counter').text($('.pc').length),120);
  });
  var open=()=>{$('#sb').addClass('open');$('#sbOverlay').fadeIn(200);$('body').css('overflow','hidden');};
  var close=()=>{$('#sb').removeClass('open');$('#sbOverlay').fadeOut(200);$('body').css('overflow','');};
  $('#mobBtn').on('click',open);$('#sbOverlay').on('click',close);
  $(window).on('resize',function(){if(window.innerWidth>1024)close();if(ok)$g.isotope('layout');});
});
</script>
</body>
</html>
