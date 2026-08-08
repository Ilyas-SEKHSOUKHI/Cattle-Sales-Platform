<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ferme Tarmast — Le prix se discute, pas s'impose</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" type="image/png" href="assets/images/icon_vache.png">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --forest: #1B3A2B;
    --forest-2: #142A20;
    --cream: #FBF6EC;
    --cream-2: #F2EAD8;
    --green: #4CAF50;
    --green-dark: #3d9140;
    --ochre: #C9902F;
    --rust: #A6512E;
    --ink: #2A2A25;
    --ink-soft: #5C5B52;
    --line: #E3D9C2;

    --display: "Fraunces", Georgia, serif;
    --body: "Work Sans", Arial, Helvetica, sans-serif;
  }

  *{ margin:0; padding:0; box-sizing:border-box; }

  html{ scroll-behavior: smooth; }

  body{
    font-family: var(--body);
    background: var(--cream);
    color: var(--ink);
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
  }

  img, svg{ display:block; max-width:100%; }

  a{ color: inherit; text-decoration: none; }

  ul{ list-style:none; }

  h1,h2,h3{ font-family: var(--display); color: var(--forest); font-weight: 600; }

  .container{
    width: min(1160px, 92%);
    margin-inline: auto;
  }

  :focus-visible{
    outline: 3px solid var(--ochre);
    outline-offset: 3px;
  }

  /* ---------- Fence-style divider ---------- */
  .fence-divider{
    height: 22px;
    background-image: repeating-linear-gradient(
      to right,
      var(--line) 0 2px,
      transparent 2px 26px
    );
    background-position: bottom;
    background-repeat: repeat-x;
    background-size: auto 100%;
    opacity: .9;
  }

  /* ================= NAVBAR ================= */
  .navbar{
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(251,246,236,.92);
    backdrop-filter: blur(6px);
    border-bottom: 1px solid var(--line);
  }
  .navbar .container{
    display:flex;
    align-items:center;
    justify-content: space-between;
    height: 74px;
  }
  .brand{
    display:flex;
    align-items:center;
    gap:.6rem;
    font-family: var(--display);
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--forest);
  }
  .brand-mark{
    width: 38px; height: 38px;
    flex-shrink: 0;
  }
  .nav-links{
    display:flex;
    gap: 2rem;
    align-items:center;
  }
  .nav-links a{
    font-size: .95rem;
    font-weight: 500;
    color: var(--ink-soft);
    transition: color .2s;
    position: relative;
  }
  .nav-links a:hover{ color: var(--forest); }
  .nav-actions{
    display:flex;
    align-items:center;
    gap: 1.1rem;
  }
  .btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap: .5rem;
    padding: .68rem 1.3rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: .92rem;
    border: none;
    cursor: pointer;
    transition: background .25s, color .25s, box-shadow .25s, transform .15s;
    font-family: var(--body);
  }
  .btn-login{
    background: transparent;
    color: var(--forest);
    font-weight: 600;
  }
  .btn-login:hover{ color: var(--green-dark); }
  .btn-register{
    background: var(--green);
    color: #fff;
    box-shadow: 0 6px 16px rgba(76,175,80,.28);
  }
  .btn-register:hover{ background: var(--green-dark); transform: translateY(-1px); }

  .nav-toggle{
    display:none;
    background:none;
    border:none;
    cursor:pointer;
    flex-direction:column;
    gap:5px;
    padding: 8px;
  }
  .nav-toggle span{
    width: 24px; height: 2px; background: var(--forest); display:block; border-radius: 2px;
  }

  /* ================= HERO ================= */
  .hero{
    padding: 2rem 0 4rem;
    position: relative;
    overflow: hidden;
  }
  .hero::before{
    content:"";
    position:absolute;
    inset: 0;
    background:
      radial-gradient(600px 300px at 85% 10%, rgba(201,144,47,.14), transparent 70%),
      radial-gradient(500px 260px at 10% 90%, rgba(76,175,80,.10), transparent 70%);
    pointer-events: none;
  }
  .hero .container{
    display:grid;
    grid-template-columns: 1.05fr .95fr;
    gap: 3.5rem;
    align-items:center;
    position: relative;
  }
  .eyebrow{
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    font-size: .78rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--rust);
    background: rgba(166,81,46,.09);
    padding: .4rem .8rem;
    border-radius: 999px;
    margin-bottom: 1.4rem;
  }
  .eyebrow .dot{
    width:6px; height:6px; border-radius:50%; background: var(--rust);
  }
  .hero h1{
    font-size: clamp(2.1rem, 3.6vw, 3.2rem);
    line-height: 1.12;
    letter-spacing: -.01em;
    margin-bottom: 1.3rem;
  }
  .hero h1 em{
    font-style: normal;
    color: var(--green-dark);
    background: linear-gradient(180deg, transparent 62%, rgba(76,175,80,.25) 62%);
  }
  .hero p.lead{
    font-size: 1.08rem;
    color: var(--ink-soft);
    max-width: 46ch;
    margin-bottom: 2.1rem;
  }
  .hero-ctas{
    display:flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2.4rem;
  }
  .btn-lg{
    padding: .95rem 1.7rem;
    font-size: .98rem;
    border-radius: 10px;
  }
  .btn-ghost{
    background: transparent;
    color: var(--forest);
    border: 1.5px solid var(--line);
  }
  .btn-ghost:hover{ border-color: var(--forest); background: rgba(27,58,43,.04); }

  .trust-row{
    display:flex;
    gap: 1.6rem;
    flex-wrap: wrap;
    font-size: .85rem;
    color: var(--ink-soft);
  }
  .trust-row li{ display:flex; align-items:center; gap:.45rem; }
  .trust-row svg{ width:16px; height:16px; color: var(--green-dark); flex-shrink:0; }

  /* --- Hero visual: listing card + ear-tag stamp --- */
  .hero-visual{
    position: relative;
    display:flex;
    justify-content:center;
    align-items:center;
  }
  .listing-card{
    width: 100%;
    max-width: 380px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 50px rgba(27,58,43,.15);
    overflow: hidden;
    border: 1px solid var(--line);
    transform: rotate(-2deg);
    position: relative;
  }
  .listing-photo{
    height: 190px;
    background:
      linear-gradient(180deg, rgba(27,58,43,.0), rgba(27,58,43,.35)),
      linear-gradient(135deg, #cfe7c9, #a9cf9f);
    position: relative;
    overflow: hidden;
  }
  .listing-photo .cow-photo{
    position:absolute; inset:0;
    width:100%; height:100%;
    object-fit: cover;
  }
  .listing-tag{
    position:absolute;
    top: 12px; left: 12px;
    background: var(--forest);
    color:#fff;
    font-size:.72rem;
    font-weight:700;
    letter-spacing:.04em;
    padding: .3rem .6rem;
    border-radius: 999px;
    z-index: 2;
  }
  .listing-body{ padding: 1.2rem 1.3rem 1.4rem; }
  .listing-body h4{
    font-family: var(--display);
    color: var(--forest);
    font-size: 1.15rem;
    margin-bottom: .2rem;
  }
  .listing-body .meta{
    font-size: .82rem;
    color: var(--ink-soft);
    margin-bottom: .9rem;
  }
  .listing-price-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom: .9rem;
  }
  .listing-price-row .label{
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--ink-soft);
    font-weight:600;
  }
  .listing-price-row .price{
    font-family: var(--display);
    font-size: 1.3rem;
    color: var(--forest);
    font-weight: 700;
  }
  .offer-box{
    display:flex;
    gap: .5rem;
    background: var(--cream-2);
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: .5rem;
  }
  .offer-box input{
    flex:1;
    border:none;
    background:transparent;
    padding: .4rem .5rem;
    font-family: var(--body);
    font-size: .9rem;
    color: var(--ink);
  }
  .offer-box input:focus{ outline:none; }
  .offer-box button{
    background: var(--green);
    color:#fff;
    border:none;
    border-radius: 7px;
    padding: .5rem 1rem;
    font-weight:700;
    font-size:.85rem;
    cursor:pointer;
    transition: background .2s;
  }
  .offer-box button:hover{ background: var(--green-dark); }
  .hero-carousel-dots{
    display:flex;
    justify-content:center;
    gap:.45rem;
    margin-top: .9rem;
  }
  .hero-carousel-dots span{
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--line);
    transition: all .2s ease;
  }
  .hero-carousel-dots span.active{
    background: var(--green);
    transform: scale(1.15);
  }

  /* ear tag stamp - signature element */
  .ear-tag{
    position:absolute;
    top: -18px;
    right: -6px;
    width: 128px; height: 128px;
    z-index: 3;
  }
  @media (prefers-reduced-motion: no-preference){
    .ear-tag{ animation: sway 6s ease-in-out infinite; transform-origin: top center; }
  }
  @keyframes sway{
    0%,100%{ transform: rotate(8deg); }
    50%{ transform: rotate(2deg); }
  }

  /* ================= STATS BAR ================= */
  .stats{
    background: var(--forest);
    color: #EFE9DA;
    padding: 2.6rem 0;
  }
  .stats .container{
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    text-align:center;
  }
  .stats .num{
    font-family: var(--display);
    font-size: clamp(1.6rem, 2.4vw, 2.1rem);
    color: #fff;
    font-weight: 700;
  }
  .stats .lbl{
    font-size: .82rem;
    color: #C7D6CB;
    margin-top: .3rem;
  }

  /* ================= SECTIONS shared ================= */
  section{ padding: 5.2rem 0; }
  .section-head{
    max-width: 640px;
    margin-bottom: 3rem;
  }
  .section-head .eyebrow{ margin-bottom: 1rem; }
  .section-head h2{
    font-size: clamp(1.7rem, 2.6vw, 2.3rem);
    margin-bottom: .9rem;
  }
  .section-head p{
    color: var(--ink-soft);
    font-size: 1.02rem;
  }

  /* ---- How it works ---- */
  .steps{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.8rem;
  }
  .step{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 2rem 1.7rem;
    position: relative;
  }
  .step .step-num{
    font-family: var(--display);
    font-size: 2.6rem;
    font-weight: 700;
    color: var(--cream-2);
    -webkit-text-stroke: 1.5px var(--ochre);
    color: transparent;
    line-height:1;
    margin-bottom: 1.1rem;
    display:block;
  }
  .step h3{
    font-size: 1.15rem;
    margin-bottom: .6rem;
  }
  .step p{ color: var(--ink-soft); font-size: .95rem; }
  .step-arrow{
    position:absolute;
    top: 2.1rem;
    right: -1.35rem;
    width: 26px; height: 26px;
    color: var(--line);
  }
  .step:last-child .step-arrow{ display:none; }

  /* ---- Products (dairy) ---- */
  .products-marquee{
    overflow: hidden;
    position: relative;
    mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
  }
  .products-track{
    display:flex;
    width: max-content;
    gap: 1.3rem;
    animation: products-scroll 24s linear infinite;
  }
  .product-card{
    flex: 0 0 240px;
    background: var(--cream-2);
    border-radius: 14px;
    padding: 1.5rem 1.3rem;
    border: 1px solid var(--line);
    transition: transform .2s, box-shadow .2s;
  }
  .product-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(27,58,43,.1);
  }
  .product-icon{
    width: 46px; height:46px;
    margin-bottom: 1rem;
    color: var(--forest);
  }
  .product-card h4{
    font-family: var(--display);
    color: var(--forest);
    font-size: 1.08rem;
    margin-bottom: .3rem;
  }
  .product-card .code{
    font-size: .72rem;
    color: var(--ochre);
    font-weight: 700;
    letter-spacing: .05em;
    display:block;
    margin: 1rem 0 1rem;
  }
  .product-card p{ font-size: .88rem; color: var(--ink-soft); margin-top: .25rem; }

  @keyframes products-scroll{
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
  }

  /* ---- About the farm ---- */
  .about{
    background: var(--cream-2);
  }
  .about .container{
    display:grid;
    grid-template-columns: .9fr 1.1fr;
    gap: 3.5rem;
    align-items:center;
  }
  .about-visual{
    background: var(--forest);
    border-radius: 20px;
    aspect-ratio: 4/3.4;
    position:relative;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .about-visual img{
    position:absolute; inset:0;
    width:100%; height:100%;
    object-fit: cover;
    opacity: .9;
  }
  .about-visual svg{ width: 62%; position:relative; z-index:1; }
  .about-visual::after{
    content:"Ferme Tarmast · Maroc";
    position:absolute;
    bottom: 1.1rem; left: 1.3rem;
    color: #EFE9DA;
    font-family: var(--display);
    font-size: .95rem;
    letter-spacing: .03em;
    z-index: 2;
  }
  .about-text h2{ margin-bottom: 1.1rem; }
  .about-text p{ color: var(--ink-soft); margin-bottom: 1rem; }
  .about-list{
    margin-top: 1.3rem;
    display:grid;
    gap: .8rem;
  }
  .about-list li{
    display:flex;
    gap: .7rem;
    align-items:flex-start;
    font-size: .93rem;
    color: var(--ink);
  }
  .about-list svg{ width:18px; height:18px; color: var(--green-dark); margin-top: .2rem; flex-shrink:0; }

  /* ---- SIAM 2025 Section ---- */
  .siam-section {
    background: #fff;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    padding: 5rem 0;
  }
  .siam-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 3rem;
    align-items: center;
  }
  .siam-slider-container {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: var(--cream-2);
    border: 1px solid var(--line);
    box-shadow: 0 16px 36px rgba(27,58,43,.12);
  }
  .siam-slide-viewport {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: #142A20;
  }
  .siam-slide-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
  }
  .siam-slide-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: rgba(27,58,43,0.85);
    backdrop-filter: blur(4px);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    letter-spacing: 0.04em;
    z-index: 5;
    border: 1px solid rgba(255,255,255,0.2);
  }
  .siam-slide-counter {
    position: absolute;
    top: 14px;
    right: 14px;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(4px);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    z-index: 5;
  }
  .siam-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    color: var(--forest);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
    z-index: 6;
  }
  .siam-nav-btn:hover {
    background: #fff;
    transform: translateY(-50%) scale(1.08);
    color: var(--green-dark);
  }
  .siam-nav-btn.prev { left: 12px; }
  .siam-nav-btn.next { right: 12px; }

  .siam-thumbnails {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem;
    background: var(--cream);
    overflow-x: auto;
    scrollbar-width: thin;
  }
  .siam-thumb {
    width: 64px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.65;
    transition: all 0.2s ease;
  }
  .siam-thumb:hover { opacity: 0.95; }
  .siam-thumb.active {
    border-color: var(--ochre);
    opacity: 1;
    transform: scale(1.05);
  }
  .siam-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .siam-trophies {
    display: grid;
    gap: 0.9rem;
    margin: 1.3rem 0;
  }
  .trophy-card {
    display: flex;
    gap: 0.9rem;
    align-items: flex-start;
    background: var(--cream);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 0.9rem 1rem;
    transition: transform 0.2s, border-color 0.2s;
  }
  .trophy-card:hover {
    transform: translateX(4px);
    border-color: var(--ochre);
  }
  .trophy-icon {
    font-size: 1.5rem;
    line-height: 1;
    flex-shrink: 0;
    background: rgba(201,144,47,0.12);
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .trophy-info h4 {
    font-family: var(--display);
    font-size: 0.95rem;
    color: var(--forest);
    margin-bottom: 0.2rem;
  }
  .trophy-info p {
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.45;
  }
  .siam-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 1.2rem;
  }
  .siam-tag {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--forest);
    background: rgba(76,175,80,0.12);
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
  }
  .cta-band{
    background: linear-gradient(120deg, var(--forest), var(--forest-2));
    color: #fff;
    text-align:center;
    border-radius: 22px;
    padding: 3.4rem 2rem;
    position: relative;
    overflow:hidden;
  }
  .cta-band::before{
    content:"";
    position:absolute;
    inset:0;
    background-image: repeating-linear-gradient(45deg, rgba(255,255,255,.035) 0 2px, transparent 2px 26px);
  }
  .cta-band h2{ color:#fff; margin-bottom: .8rem; position:relative; }
  .cta-band p{ color: #C7D6CB; max-width: 46ch; margin: 0 auto 1.8rem; position:relative; }
  .cta-actions{ display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; position:relative; }
  .btn-cream{
    background: #fff;
    color: var(--forest);
  }
  .btn-cream:hover{ background: var(--cream-2); }
  .btn-outline-light{
    background: transparent;
    color:#fff;
    border: 1.5px solid rgba(255,255,255,.4);
  }
  .btn-outline-light:hover{ border-color:#fff; }

  /* ================= FOOTER ================= */
  footer{
    background: var(--forest-2);
    color: #C7D6CB;
    padding: 3.5rem 0 2rem;
  }
  .footer-grid{
    display:grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
    gap: 2.5rem;
    margin-bottom: 2.5rem;
  }
  .footer-brand{
    display:flex; align-items:center; gap:.55rem;
    font-family: var(--display);
    color:#fff;
    font-size: 1.15rem;
    font-weight:700;
    margin-bottom: .9rem;
  }
  .footer-brand svg{ width:30px; height:30px; }
  footer p.desc{ font-size: .88rem; color:#9FB3A6; max-width: 30ch; }
  footer h5{
    color:#fff;
    font-size: .85rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 1rem;
  }
  footer ul li{ margin-bottom: .6rem; }
  footer ul a{ font-size: .89rem; color: #B7C7BA; transition: color .2s; }
  footer ul a:hover{ color:#fff; }
  .footer-bottom{
    border-top: 1px solid rgba(255,255,255,.1);
    padding-top: 1.6rem;
    display:flex;
    justify-content:space-between;
    flex-wrap:wrap;
    gap: .8rem;
    font-size: .82rem;
    color: #8FA595;
  }

  /* ================= RESPONSIVE ================= */
  @media (max-width: 980px){
    .hero .container{ grid-template-columns: 1fr; }
    .hero-visual{ order:-1; max-width: 420px; margin-inline:auto; }
    .about .container{ grid-template-columns: 1fr; }
    .about-visual{ max-width: 420px; margin-inline:auto; width:100%; }
    .siam-grid{ grid-template-columns: 1fr; }
    .stats .container{ grid-template-columns: repeat(2,1fr); }
    .products-grid{ grid-template-columns: repeat(2,1fr); }
    .footer-grid{ grid-template-columns: 1fr 1fr; }
  }

  @media (max-width: 760px){
    .nav-links, .nav-actions .btn-login{ display:none; }
    .nav-toggle{ display:flex; }
    .steps{ grid-template-columns: 1fr; }
    .step-arrow{ display:none; }
  }

  @media (max-width: 520px){
    .stats .container{ grid-template-columns: 1fr 1fr; }
    .products-grid{ grid-template-columns: 1fr 1fr; }
    .footer-grid{ grid-template-columns: 1fr; }
    .cta-actions{ flex-direction:column; }
    .cta-actions .btn{ width:100%; }
  }
</style>
</head>
<body>

<!-- ================= NAVBAR ================= -->
<header class="navbar">
  <div class="container">
    <a href="#" class="brand">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="24" cy="24" r="23" fill="#4CAF50" opacity="0.12"/>
        <path d="M14 22c0-2 1.5-3.5 3.5-3.5 1 0 1.8.4 2.5 1 1-1.3 2.5-2 4-2s3 .7 4 2c.7-.6 1.5-1 2.5-1 2 0 3.5 1.5 3.5 3.5 0 1-.4 2-1 2.6.6.5 1 1.3 1 2.2 0 2-1.7 3.7-3.7 3.7H18.7C16.7 30.5 15 28.8 15 26.8c0-.9.4-1.7 1-2.2-.6-.6-1-1.6-1-2.6z" fill="#1B3A2B"/>
        <circle cx="20" cy="25" r="1.4" fill="#fff"/>
        <circle cx="28" cy="25" r="1.4" fill="#fff"/>
        <path d="M22.5 27.5c.5.6 1.5.6 2 0" stroke="#fff" stroke-width="1" stroke-linecap="round"/>
      </svg>
      Ferme Tarmast
    </a>

    <nav class="nav-links">
      <a href="#comment-ca-marche">Comment ça marche</a>
      <a href="#cheptel">Le cheptel</a>
      <a href="#produits">Produits</a>
      <a href="#apropos">À propos</a>
      <a href="#siam2025">SIAM 2025</a>
      <a href="#contact">Contact</a>
    </nav>

    <div class="nav-actions">
      <a href="login.php" class="btn btn-login">Connexion</a>
      <a href="register.php" class="btn btn-register">Inscription</a>
    </div>

    <button class="nav-toggle" aria-label="Ouvrir le menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
<!--Banner Ferme Tarmast-->
<img src="assets/images/banner-ferme-tarmast.jpeg" alt="Banner Ferme Tarmast" style="display:block; width:100%; height:auto; margin:0 0 0.5rem;">
<!-- ================= HERO ================= -->
<section class="hero">
  <div class="container">
    <div class="hero-copy">
      <span class="eyebrow"><span class="dot"></span> Ferme laitière · Maroc</span>
      <h1>Achetez un bovin <em>directement à la ferme.</em><br>Vous proposez le prix.</h1>
      <p class="lead">
        Ferme Tarmast élève son propre cheptel et produit tout ce qui touche au lait. Quand
        l'exploitation décide de vendre une vache, elle publie sa fiche ici — vous parcourez
        le cheptel disponible et envoyez votre offre, en toute transparence.
      </p>
      <div class="hero-ctas">
        <a href="#cheptel" class="btn btn-register btn-lg">Voir le cheptel disponible</a>
        <a href="#comment-ca-marche" class="btn btn-ghost btn-lg">Comment ça marche</a>
      </div>
      <ul class="trust-row">
        <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Vaches suivies et fichées individuellement</li>
        <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Offres validées directement par la ferme</li>
      </ul>
    </div>

    <div class="hero-visual">
      <svg class="ear-tag" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M60 6c-8 0-14 6-14 14v8h28v-8c0-8-6-14-14-14z" fill="#C9902F"/>
        <circle cx="60" cy="66" r="46" fill="#fff" stroke="#C9902F" stroke-width="3"/>
        <circle cx="60" cy="66" r="38" fill="none" stroke="#C9902F" stroke-width="1" stroke-dasharray="3 4"/>
        <text x="60" y="58" text-anchor="middle" font-family="Fraunces, serif" font-size="11" fill="#1B3A2B" font-weight="700">TARMAST</text>
        <text x="60" y="76" text-anchor="middle" font-family="Work Sans, sans-serif" font-size="18" fill="#A6512E" font-weight="700">N° 0231</text>
      </svg>

      <div>
        <div class="listing-card" id="heroCarouselCard">
          <div class="listing-photo">
            <span class="listing-tag" id="heroCarouselTag">N° 0231 · Disponible</span>
            <img class="cow-photo" id="heroCarouselImage" src="assets/images/vache/vache_1.jpg" alt="Vache disponible à la Ferme Tarmast" onerror="this.src='assets/images/vache/vache.jpg'">
          </div>
          <div class="listing-body">
            <h4 id="heroCarouselTitle">Vache laitière — Holstein</h4>
            <p class="meta" id="heroCarouselMeta">4 ans · 620 kg · ~26 L/jour · Ferme Tarmast</p>
            <div class="listing-price-row">
              <span class="label">Prix demandé</span>
              <span class="price" id="heroCarouselPrice">18 500 DH</span>
            </div>
            <div class="offer-box">
              <input type="text" placeholder="Votre offre (DH)" aria-label="Votre offre en dirhams">
              <button type="button">Proposer</button>
            </div>
          </div>
        </div>
        <div class="hero-carousel-dots" aria-label="Sélection de vaches" id="heroCarouselDots"></div>
      </div>
    </div>
  </div>
</section>

<div class="fence-divider"></div>

<script>
  const heroSlides = [
    {
      image: 'assets/images/vache/vache_1.jpg',
      tag: 'N° 0231 · Disponible',
      title: 'Vache laitière — Holstein',
      meta: '4 ans · 620 kg · ~26 L/jour · Ferme Tarmast',
      price: '18 500 DH'
    },
    {
      image: 'assets/images/vache/vache_2.jpg',
      tag: 'N° 0418 · Disponible',
      title: 'Velle robuste — Montbéliarde',
      meta: '3 ans · 590 kg · ~24 L/jour · Ferme Tarmast',
      price: '16 800 DH'
    },
    {
      image: 'assets/images/vache/vache_3.jpg',
      tag: 'N° 0786 · Disponible',
      title: 'Vache de race — Charolaise',
      meta: '5 ans · 710 kg · ~28 L/jour · Ferme Tarmast',
      price: '21 200 DH'
    },
    {
      image: 'assets/images/vache/vache_4.jpg',
      tag: 'N° 0942 · Disponible',
      title: 'Vache de production — Holstein',
      meta: '6 ans · 680 kg · ~27 L/jour · Ferme Tarmast',
      price: '19 900 DH'
    },
    {
      image: 'assets/images/vache/vache_5.jpg',
      tag: 'N° 1024 · Disponible',
      title: 'Vache jeune — Montbéliarde',
      meta: '2 ans · 540 kg · ~22 L/jour · Ferme Tarmast',
      price: '15 300 DH'
    }
  ];

  const heroImage = document.getElementById('heroCarouselImage');
  const heroTag = document.getElementById('heroCarouselTag');
  const heroTitle = document.getElementById('heroCarouselTitle');
  const heroMeta = document.getElementById('heroCarouselMeta');
  const heroPrice = document.getElementById('heroCarouselPrice');
  const heroDots = document.getElementById('heroCarouselDots');

  if (heroImage && heroTag && heroTitle && heroMeta && heroPrice && heroDots) {
    heroSlides.forEach((_, index) => {
      const dot = document.createElement('span');
      dot.dataset.index = String(index);
      if (index === 0) dot.classList.add('active');
      heroDots.appendChild(dot);
    });

    let currentSlide = 0;

    const renderSlide = () => {
      const slide = heroSlides[currentSlide];
      heroImage.src = slide.image;
      heroImage.alt = slide.title;
      heroTag.textContent = slide.tag;
      heroTitle.textContent = slide.title;
      heroMeta.textContent = slide.meta;
      heroPrice.textContent = slide.price;

      Array.from(heroDots.children).forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
      });
    };

    setInterval(() => {
      currentSlide = (currentSlide + 1) % heroSlides.length;
      renderSlide();
    }, 5000);

    renderSlide();
  }
</script>

<!-- ================= STATS ================= -->
<section class="stats">
  <div class="container">
    <div>
      <div class="num">180+</div>
      <div class="lbl">têtes suivies à la ferme</div>
    </div>
    <div>
      <div class="num">2 400 L</div>
      <div class="lbl">production laitière quotidienne</div>
    </div>
    <div>
      <div class="num">12 ans</div>
      <div class="lbl">d'activité</div>
    </div>
    <div>
      <div class="num">6 régions</div>
      <div class="lbl">livrées à travers le Maroc</div>
    </div>
  </div>
</section>

<!-- ================= COMMENT CA MARCHE ================= -->
<section id="comment-ca-marche">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><span class="dot"></span> Le processus</span>
      <h2>Trois étapes, du cheptel à la vente</h2>
      <p>Chaque vache dispose de sa propre fiche. Vous consultez, vous proposez, la ferme décide — sans intermédiaire caché.</p>
    </div>

    <div class="steps">
      <div class="step">
        <span class="step-num">01</span>
        <h3>Parcourez le cheptel</h3>
        <p>Race, âge, poids, production laitière et photo pour chaque vache disponible à Ferme Tarmast.</p>
        <svg class="step-arrow" viewBox="0 0 24 24" fill="none"><path d="M4 12h16m0 0l-6-6m6 6l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="step">
        <span class="step-num">02</span>
        <h3>Proposez votre prix</h3>
        <p>Envoyez votre offre directement depuis la fiche de la vache qui vous intéresse.</p>
        <svg class="step-arrow" viewBox="0 0 24 24" fill="none"><path d="M4 12h16m0 0l-6-6m6 6l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="step">
        <span class="step-num">03</span>
        <h3>La ferme valide la vente</h3>
        <p>Ferme Tarmast accepte, ajuste ou décline votre offre. Une fois validée, la vente est conclue.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= PRODUITS ================= -->
<section id="produits">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><span class="dot"></span> Au-delà du cheptel</span>
      <h2>Nos produit</h2>
      <p>Découvrez les catégories de bovins disponibles à Ferme Tarmast, de la vache au bœuf.</p>
    </div>

    <div class="products-marquee">
      <div class="products-track">
        <div class="product-card">
          <img src="assets/images/lait_cru.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 01</span>
          <h4>Lait cru</h4>
          <p>Vache de race laitière sélectionnée pour sa production.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/vache_1.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 02</span>
          <h4>Vache</h4>
          <p>Femelle adulte élevée pour la reproduction et la production.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/veau.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 03</span>
          <h4>Veau / Velle</h4>
          <p>Jeune bovin en croissance, prêt à rejoindre le cheptel.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/boeuf.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 04</span>
          <h4>Bœuf</h4>
          <p>Variantes du cheptel selon l’âge, le sexe et l’usage.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/lait_cru.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 01</span>
          <h4>Lait cru</h4>
          <p>Vache de race laitière sélectionnée pour sa production.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/vache_1.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 02</span>
          <h4>Vache</h4>
          <p>Femelle adulte élevée pour la reproduction et la production.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/veau.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 03</span>
          <h4>Veau / Velle</h4>
          <p>Jeune bovin en croissance, prêt à rejoindre le cheptel.</p>
        </div>
        <div class="product-card">
          <img src="assets/images/boeuf.jpg" alt="" style="border-radius: 10px;">
          <span class="code">CAT 04</span>
          <h4>Bœuf</h4>
          <p>Variantes du cheptel selon l’âge, le sexe et l’usage.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= A PROPOS ================= -->
<section class="about" id="apropos">
  <div class="container">
    <div class="about-visual">
      <!-- Vraie photo de la ferme : remplace le src ci-dessous.
           Si absente, l'illustration SVG reste visible en fond. -->
      <img src="assets/images/ferme-tarmast.jpg" alt="Vue de la Ferme Tarmast" onerror="this.style.display='none'">
      <svg viewBox="0 0 200 170" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 130c40-40 120-40 160 0" stroke="#EFE9DA" stroke-width="2" stroke-dasharray="4 6" opacity=".4"/>
        <path d="M30 100l40-50 40 50" stroke="#EFE9DA" stroke-width="3" fill="none" opacity=".5"/>
        <path d="M90 100l30-40 30 40" stroke="#EFE9DA" stroke-width="3" fill="none" opacity=".35"/>
        <circle cx="150" cy="35" r="16" fill="#C9902F" opacity=".85"/>
        <ellipse cx="100" cy="120" rx="70" ry="10" fill="#0F241A"/>
      </svg>
    </div>
    <div class="about-text">
      <span class="eyebrow"><span class="dot"></span> À propos</span>
      <h2>Ferme Tarmast, une exploitation laitière marocaine</h2>
      <p>
        Ferme Tarmast produit tout ce qui touche au lait : collecte, transformation et distribution
        de produits laitiers à travers le Maroc. Le cheptel vit et est suivi directement sur place.
      </p>
      <p>
        Il arrive que la ferme choisisse de vendre certaines de ses vaches. Cette plateforme permet
        alors d'ajouter ou de retirer une vache du marché à tout moment, et aux acheteurs de proposer
        directement leur prix sur chaque fiche.
      </p>
      <ul class="about-list">
        <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Gestion du cheptel entièrement contrôlée par la ferme</li>
        <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Vente déclenchée uniquement quand l'exploitation le décide</li>
        <li><svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Offres d'achat transparentes, fiche par fiche</li>
      </ul>
    </div>
  </div>
</section>

<div class="fence-divider"></div>

<!-- ================= SIAM 2025 ================= -->
<section class="siam-section" id="siam2025">
  <div class="container">
    <div class="section-head" style="max-width: 820px;">
      <span class="eyebrow"><span class="dot"></span> 🏆 Distinctions &amp; Événements · SIAM 2025</span>
      <h2>Fiers de notre réussite au SIAM 2025 — 17ᵉ édition !</h2>
      <p>JIBAL et sa ferme TARMAST ont brillé lors du Salon International de l’Agriculture au Maroc grâce à des résultats exceptionnels au niveau national.</p>
    </div>

    <div class="siam-grid">
      <!-- Diaporama dynamique -->
      <div class="siam-slider-container" id="siamSlider">
        <div class="siam-slide-viewport">
          <span class="siam-slide-badge">SIAM 2025 · 17ᵉ Édition</span>
          <span class="siam-slide-counter" id="siamCounter">1 / 7</span>
          <img class="siam-slide-img" id="siamMainImage" src="assets/images/siam/1.png" alt="Participation de la Ferme Tarmast au SIAM 2025">
          <button class="siam-nav-btn prev" id="siamPrevBtn" aria-label="Photo précédente">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button class="siam-nav-btn next" id="siamNextBtn" aria-label="Photo suivante">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <div class="siam-thumbnails" id="siamThumbs"></div>
      </div>

      <!-- Contenu et Palmarès -->
      <div class="siam-content">
        <h3 style="font-family: var(--display); color: var(--forest); font-size: 1.35rem; margin-bottom: 0.8rem;">Palmarès &amp; Distinctions Majeures</h3>
        <p style="color: var(--ink-soft); font-size: 0.93rem; line-height: 1.6;">
          Une reconnaissance au plus haut niveau récompensant le travail d'excellence accompli par nos équipes sur notre cheptel et notre production laitière.
        </p>

        <div class="siam-trophies">
          <div class="trophy-card">
            <div class="trophy-icon">🏆</div>
            <div class="trophy-info">
              <h4>1ᵉʳᵉ Place — Champion des Champions au Maroc</h4>
              <p>Décernée à notre vache <strong>Holstein multipare</strong>, remise officielle par le Ministère de l’Agriculture.</p>
            </div>
          </div>

          <div class="trophy-card">
            <div class="trophy-icon">🥇</div>
            <div class="trophy-info">
              <h4>1ᵉʳᵉ &amp; 2ᵉᵐᵉ Places — Génisses Montbéliardes</h4>
              <p>Double podium national remporté pour nos <strong>génisses Montbéliardes gestantes</strong>.</p>
            </div>
          </div>

          <div class="trophy-card">
            <div class="trophy-icon">🎖️</div>
            <div class="trophy-info">
              <h4>Remise du Trophée par le Premier Ministre</h4>
              <p>Reçu au nom de la ferme TARMAST par Khalid Maataoui lors du dîner royal, avec les félicitations à Mohamed Ouhsaine, à l'équipe SOURCING et à Bouchta Kharbouch.</p>
            </div>
          </div>
        </div>

        <p style="font-size: 0.88rem; color: var(--ink-soft); font-style: italic; border-left: 3px solid var(--ochre); padding-left: 0.8rem; margin-top: 1rem;">
          « Un grand bravo à toutes les équipes de JIBAL et de TARMAST ainsi qu'à GRUPA POLMLEK pour cette belle performance ! Rendez-vous l’année prochaine pour la 18ᵉ édition ! »
        </p>

        <div class="siam-tags">
          <span class="siam-tag">#SIAM2025</span>
          <span class="siam-tag">#JIBAL</span>
          <span class="siam-tag">#FERMETARMAST</span>
          <span class="siam-tag">#HOLSTEIN</span>
          <span class="siam-tag">#MONTBELIARDES</span>
          <span class="siam-tag">#SUCCES</span>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="fence-divider"></div>

<!-- ================= CTA ================= -->
<section id="cheptel">
  <div class="container">
    <div class="cta-band">
      <h2>Prêt à consulter le cheptel de Ferme Tarmast ?</h2>
      <p>Créez votre compte pour proposer un prix sur un bovin, ou connectez-vous si vous en avez déjà un.</p>
      <div class="cta-actions">
        <a href="#" class="btn btn-cream btn-lg">Créer un compte</a>
        <a href="#" class="btn btn-outline-light btn-lg">Se connecter</a>
      </div>
    </div>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer id="contact">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="23" fill="#4CAF50" opacity="0.18"/><path d="M14 22c0-2 1.5-3.5 3.5-3.5 1 0 1.8.4 2.5 1 1-1.3 2.5-2 4-2s3 .7 4 2c.7-.6 1.5-1 2.5-1 2 0 3.5 1.5 3.5 3.5 0 1-.4 2-1 2.6.6.5 1 1.3 1 2.2 0 2-1.7 3.7-3.7 3.7H18.7C16.7 30.5 15 28.8 15 26.8c0-.9.4-1.7 1-2.2-.6-.6-1-1.6-1-2.6z" fill="#fff"/></svg>
          Ferme Tarmast
        </div>
        <p class="desc">Une plateforme pour la gestion et la vente du cheptel laitier, au Maroc.</p>
      </div>
      <div>
        <h5>Plateforme</h5>
        <ul>
          <li><a href="#comment-ca-marche">Comment ça marche</a></li>
          <li><a href="#cheptel">Le cheptel</a></li>
          <li><a href="#produits">Produits laitiers</a></li>
        </ul>
      </div>
      <div>
        <h5>Ferme</h5>
        <ul>
          <li><a href="#apropos">À propos de la ferme</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>
      <div>
        <h5>Compte</h5>
        <ul>
          <li><a href="login.php">Connexion</a></li>
          <li><a href="register.php">Inscription</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Ferme Tarmast. Tous droits réservés.</span>
      <span>Maroc</span>
    </div>
  </div>
</footer>

<script>
  const toggle = document.querySelector('.nav-toggle');
  const links = document.querySelector('.nav-links');
  if (toggle && links) {
    toggle.addEventListener('click', () => {
      const open = links.style.display === 'flex';
      links.style.display = open ? 'none' : 'flex';
      links.style.cssText += open ? '' : 'position:absolute;top:74px;left:0;right:0;background:#FBF6EC;flex-direction:column;padding:1.2rem 6%;border-bottom:1px solid #E3D9C2;gap:1rem;';
    });
  }

  // SIAM 2025 Dynamic Carousel
  const siamImages = [
    'assets/images/siam/1.png',
    'assets/images/siam/2.png',
    'assets/images/siam/3.png',
    'assets/images/siam/4.png',
    'assets/images/siam/5.png',
    'assets/images/siam/6.png',
    'assets/images/siam/7.png'
  ];

  const siamMainImage = document.getElementById('siamMainImage');
  const siamCounter = document.getElementById('siamCounter');
  const siamThumbsContainer = document.getElementById('siamThumbs');
  const siamPrevBtn = document.getElementById('siamPrevBtn');
  const siamNextBtn = document.getElementById('siamNextBtn');
  const siamSlider = document.getElementById('siamSlider');

  if (siamMainImage && siamThumbsContainer && siamPrevBtn && siamNextBtn) {
    let currentSiamIndex = 0;
    let siamAutoTimer = null;

    siamImages.forEach((src, idx) => {
      const thumb = document.createElement('div');
      thumb.className = `siam-thumb ${idx === 0 ? 'active' : ''}`;
      thumb.dataset.index = String(idx);
      const img = document.createElement('img');
      img.src = src;
      img.alt = `Miniature SIAM 2025 photo ${idx + 1}`;
      thumb.appendChild(img);
      thumb.addEventListener('click', () => {
        currentSiamIndex = idx;
        updateSiamSlide();
      });
      siamThumbsContainer.appendChild(thumb);
    });

    const updateSiamSlide = () => {
      siamMainImage.style.opacity = '0';
      setTimeout(() => {
        siamMainImage.src = siamImages[currentSiamIndex];
        siamMainImage.style.opacity = '1';
      }, 150);

      siamCounter.textContent = `${currentSiamIndex + 1} / ${siamImages.length}`;

      Array.from(siamThumbsContainer.children).forEach((thumb, idx) => {
        thumb.classList.toggle('active', idx === currentSiamIndex);
      });
    };

    const nextSiamSlide = () => {
      currentSiamIndex = (currentSiamIndex + 1) % siamImages.length;
      updateSiamSlide();
    };

    const prevSiamSlide = () => {
      currentSiamIndex = (currentSiamIndex - 1 + siamImages.length) % siamImages.length;
      updateSiamSlide();
    };

    siamNextBtn.addEventListener('click', nextSiamSlide);
    siamPrevBtn.addEventListener('click', prevSiamSlide);

    const startSiamAutoPlay = () => {
      siamAutoTimer = setInterval(nextSiamSlide, 4500);
    };

    const stopSiamAutoPlay = () => {
      if (siamAutoTimer) clearInterval(siamAutoTimer);
    };

    if (siamSlider) {
      siamSlider.addEventListener('mouseenter', stopSiamAutoPlay);
      siamSlider.addEventListener('mouseleave', startSiamAutoPlay);
    }

    startSiamAutoPlay();
  }
</script>

</body>
</html>