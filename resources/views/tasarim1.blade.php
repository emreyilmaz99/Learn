<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ultra Fashion - Landing Page</title>
  <script src="/_sdk/element_sdk.js"></script>
  <style>
    body {
      box-sizing: border-box;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background-color: #CBC0BB;
      color: #1a1a1a;
      overflow-x: hidden;
      width: 100%;
      height: 100%;
    }

    .page-wrapper {
      width: 100%;
      min-height: 100%;
      position: relative;
      display: flex;
      flex-direction: column;
    }

    /* Header Navigation */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      padding: 2rem 4rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      background: linear-gradient(180deg, rgba(245, 243, 239, 0.95) 0%, rgba(245, 243, 239, 0) 100%);
    }

    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      color: #1a1a1a;
    }

    .nav {
      display: flex;
      gap: 3rem;
      align-items: center;
    }

    .nav-link {
      font-size: 1.1rem;
      font-weight: 500;
      color: #1a1a1a;
      text-decoration: none;
      letter-spacing: 1px;
      position: relative;
      transition: color 0.3s ease;
    }

    .nav-link:hover {
      color: #FFD700;
    }

    .nav-link sup {
      font-size: 0.6rem;
      margin-left: 2px;
      color: #FFD700;
    }

    .search-icon {
      width: 20px;
      height: 20px;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .search-icon:hover {
      transform: scale(1.1);
    }

    /* Main Content Area */
    .main-content {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8rem 4rem 4rem;
      position: relative;
    }

    /* Background Year */
    .bg-year {
      position: absolute;
      font-size: 38rem;
      font-weight: 900;
      color: rgba(26, 26, 26, 0.03);
      letter-spacing: -0.05em;
      z-index: 0;
      user-select: none;
      pointer-events: none;
    }

    /* Center Layout */
    .center-layout {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6rem;
      max-width: 1800px;
      width: 100%;
      position: relative;
      z-index: 1;
    }

    /* Keyword Lists */
    .keyword-list {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      min-width: 180px;
    }

    .keyword-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 1.15rem;
      font-weight: 400;
      color: #4a4a4a;
      letter-spacing: 0.5px;
    }

    .keyword-arrow {
      width: 20px;
      height: 1px;
      background-color: #FFD700;
      position: relative;
    }

    .keyword-arrow::after {
      content: '';
      position: absolute;
      right: -5px;
      top: 50%;
      transform: translateY(-50%);
      width: 0;
      height: 0;
      border-left: 5px solid #FFD700;
      border-top: 3px solid transparent;
      border-bottom: 3px solid transparent;
    }

    /* Model Placeholder */
    .model-placeholder {
      /* Larger model area to match mockup */
      width: 760px;
      height: 980px;
      background-color: #CBC0BB; /* match page background */
      border: 2px solid rgba(0,0,0,0.06);
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 0.5rem;
      position: relative;
      flex-shrink: 0;
      overflow: visible; /* allow the image to bleed out for composited look */
      box-shadow: 0 20px 40px rgba(0,0,0,0.08);
      border-radius: 6px;
    }

    .model-placeholder .model-image {
      position: absolute;
      left: 50%;
      top: 50%;
      /* center the image inside the placeholder */
      transform: translate(-50%, -50%);
      height: 120%;
      width: auto;
      max-width: none;
      object-fit: cover;
      display: block;
      border-radius: 4px;
      z-index: 5;
    }

    .placeholder-text {
      font-size: 1.2rem;
      color: #6a6a6a;
      line-height: 1.6;
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    .placeholder-highlight {
      color: #FFD700;
      font-weight: 700;
    }

    /* Headline Container */
    .headline-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      z-index: 30; /* ensure headline overlays the image */
      pointer-events: none;
    }

    .main-headline {
      font-size: 12rem;
      font-weight: 900;
      letter-spacing: 0.12em;
      color: rgba(26, 26, 26, 0.22);
      line-height: 0.9;
      text-transform: uppercase;
    }

    .sub-headline {
      font-size: 7.5rem;
      font-weight: 300;
      font-style: italic;
      letter-spacing: 0.04em;
      color: #1a1a1a;
      margin-top: -2rem;
      text-transform: capitalize;
    }

    /* Footer Area */
    .footer {
      padding: 2rem 4rem 3rem;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .footer-left {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .campaign-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .campaign-tagline {
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: 2px;
      color: #1a1a1a;
      text-transform: uppercase;
    }

    .engagement-count {
      font-size: 0.9rem;
      color: #FFD700;
      font-weight: 700;
      padding: 0.3rem 0.6rem;
      border: 1px solid #FFD700;
      border-radius: 12px;
    }

    .year-display {
      font-size: 5.5rem;
      font-weight: 900;
      color: #1a1a1a;
      line-height: 1;
    }

    .divider-line {
      width: 80px;
      height: 2px;
      background-color: #FFD700;
      margin-top: 0.5rem;
    }

    .footer-right {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 1.5rem;
    }

    .discount-text {
      font-size: 4rem;
      font-weight: 900;
      color: #1a1a1a;
      letter-spacing: 0.02em;
    }

    .cta-button {
      background-color: #1a1a1a;
      color: #f5f3ef;
      padding: 1.25rem 3rem;
      font-size: 1.1rem;
      font-weight: 600;
      letter-spacing: 1.5px;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: all 0.3s ease;
      text-transform: uppercase;
    }

    .cta-button:hover {
      background-color: #FFD700;
      color: #1a1a1a;
      transform: translateX(5px);
    }

    .cta-arrow {
      font-size: 1.2rem;
      transition: transform 0.3s ease;
    }

    .cta-button:hover .cta-arrow {
      transform: translateX(5px);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .bg-year {
        font-size: 24rem;
      }

      .main-headline {
        font-size: 6rem;
      }

      .sub-headline {
        font-size: 4rem;
      }

      .center-layout {
        gap: 2rem;
        flex-direction: column;
      }

      .model-placeholder {
        width: 520px;
        height: 700px;
      }
    }

    @media (max-width: 900px) {
      .header {
        padding: 1.5rem 2rem;
      }

      .nav {
        gap: 1.5rem;
      }

      .main-content {
        padding: 6rem 2rem 2rem;
      }

      .center-layout {
        flex-direction: column;
        gap: 2rem;
      }

      .keyword-list {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
      }

      .bg-year {
        font-size: 12rem;
      }

      .main-headline {
        font-size: 3.2rem;
      }

      .sub-headline {
        font-size: 2rem;
      }

      .footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 2rem;
        padding: 2rem;
      }

      .footer-right {
        align-items: flex-start;
      }

      .year-display {
        font-size: 3rem;
      }

      .discount-text {
        font-size: 2rem;
      }
    }
  </style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="https://cdn.tailwindcss.com" type="text/javascript"></script>
 </head>
 <body>
  <div class="page-wrapper"><!-- Header -->
   <header class="header">
    <div class="logo" id="brandName">
     THE NORTH FACE
    </div>
    <nav class="nav"><a href="#" class="nav-link">HOME</a> <a href="#" class="nav-link">PRODUCTS<sup>92</sup></a> <a href="#" class="nav-link">ABOUT<sup>03</sup></a> <a href="#" class="nav-link">CONTACT<sup>01</sup></a>
     <svg class="search-icon" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle> <path d="m21 21-4.35-4.35"></path>
     </svg>
    </nav>
   </header><!-- Main Content -->
   <main class="main-content"><!-- Background Year -->
    <div class="bg-year" id="bgYear">
     2025
    </div><!-- Center Layout -->
    <div class="center-layout"><!-- Left Keywords -->
     <div class="keyword-list">
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Fashion</span>
      </div>
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Ecological fashion</span>
      </div>
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Zero waste</span>
      </div>
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Planet</span>
      </div>
     </div><!-- Model Placeholder with Overlaid Headlines -->
     <div style="position: relative;">
      <div class="model-placeholder">
       {{-- Replace placeholder with actual image located at public/images/man3.png --}}
       <img src="{{ asset('man3.png') }}" alt="Model" class="model-image" />
      </div><!-- Headline Overlay -->
      <div class="headline-container">
       <div class="main-headline" id="mainHeadline">
        ULTRA FASHION
       </div>
       <div class="sub-headline" id="subHeadline">
        Evolution
       </div>
      </div>
     </div><!-- Right Keywords -->
     <div class="keyword-list">
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Smart materials</span>
      </div>
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Biomanufacturing</span>
      </div>
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Sustainable fabrics</span>
      </div>
      <div class="keyword-item">
       <div class="keyword-arrow"></div><span>Smart textiles</span>
      </div>
     </div>
    </div>
   </main><!-- Footer -->
   <footer class="footer">
    <div class="footer-left">
     <div class="campaign-info">
      <div class="campaign-tagline" id="campaignTagline">
       FASHION THAT CHANGES THE WORLD
      </div>
      <div class="engagement-count" id="engagementCount">
       +5K
      </div>
     </div>
     <div class="year-display" id="yearDisplay">
      2025
     </div>
     <div class="divider-line"></div>
    </div>
    <div class="footer-right">
     <div class="discount-text" id="discountText">
      30% Disc
     </div><button class="cta-button" id="ctaButton"> <span id="ctaText">SEE MORE COLLECTION</span> <span class="cta-arrow">→</span> </button>
    </div>
   </footer>
  </div>
  <script>
    const defaultConfig = {
      background_color: "#CBC0BB",
      text_color: "#1a1a1a",
      accent_color: "#FFD700",
      surface_color: "#e8e6e1",
      button_color: "#1a1a1a",
      font_family: "system-ui, -apple-system, sans-serif",
      font_size: 16,
      brand_name: "THE NORTH FACE",
      main_headline: "ULTRA FASHION",
      sub_headline: "Evolution",
      background_year: "2025",
      campaign_tagline: "FASHION THAT CHANGES THE WORLD",
      year: "2025",
      engagement_count: "+5K",
      discount_text: "30% Disc",
      cta_button: "SEE MORE COLLECTION"
    };

    async function onConfigChange(config) {
      const backgroundColor = config.background_color || defaultConfig.background_color;
      const textColor = config.text_color || defaultConfig.text_color;
      const accentColor = config.accent_color || defaultConfig.accent_color;
      const surfaceColor = config.surface_color || defaultConfig.surface_color;
      const buttonColor = config.button_color || defaultConfig.button_color;
      const fontFamily = config.font_family || defaultConfig.font_family;
      const fontSize = config.font_size || defaultConfig.font_size;

      // Apply colors
      document.body.style.backgroundColor = backgroundColor;
      document.body.style.color = textColor;

      // Apply font
      const fontStack = `${fontFamily}, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`;
      document.body.style.fontFamily = fontStack;

      // Update all text elements with scaled font sizes
      document.querySelector('.logo').style.fontSize = `${fontSize * 0.6875}px`; // 1.1rem = 17.6px at 16px base
      document.querySelector('.logo').style.color = textColor;
      
      document.querySelectorAll('.nav-link').forEach(link => {
        link.style.fontSize = `${fontSize * 0.53125}px`; // 0.85rem
        link.style.color = textColor;
      });
      
      document.querySelectorAll('.nav-link sup').forEach(sup => {
        sup.style.color = accentColor;
      });

      document.querySelector('.bg-year').style.color = `${textColor}08`;
      
      document.querySelectorAll('.keyword-item').forEach(item => {
        item.style.fontSize = `${fontSize * 0.5625}px`; // 0.9rem
      });
      
      document.querySelectorAll('.keyword-arrow').forEach(arrow => {
        arrow.style.backgroundColor = accentColor;
      });

      document.querySelectorAll('.keyword-arrow::after').forEach(arrow => {
        arrow.style.borderLeftColor = accentColor;
      });

      const placeholder = document.querySelector('.model-placeholder');
      placeholder.style.background = `linear-gradient(135deg, ${surfaceColor} 0%, ${adjustBrightness(surfaceColor, -10)} 100%)`;

      document.querySelector('.main-headline').style.color = `${textColor}40`;
      document.querySelector('.sub-headline').style.color = textColor;
      document.querySelector('.main-headline').style.fontSize = `${fontSize * 4.375}px`; // 7rem
      document.querySelector('.sub-headline').style.fontSize = `${fontSize * 3.125}px`; // 5rem

      document.querySelector('.campaign-tagline').style.color = textColor;
      document.querySelector('.campaign-tagline').style.fontSize = `${fontSize * 0.46875}px`; // 0.75rem
      
      document.querySelector('.engagement-count').style.color = accentColor;
      document.querySelector('.engagement-count').style.borderColor = accentColor;
      
      document.querySelector('.year-display').style.color = textColor;
      document.querySelector('.year-display').style.fontSize = `${fontSize * 2.5}px`; // 4rem
      
      document.querySelector('.divider-line').style.backgroundColor = accentColor;
      
      document.querySelector('.discount-text').style.color = textColor;
      document.querySelector('.discount-text').style.fontSize = `${fontSize * 1.875}px`; // 3rem
      
      const ctaButton = document.querySelector('.cta-button');
      ctaButton.style.backgroundColor = buttonColor;
      ctaButton.style.color = backgroundColor;
      ctaButton.style.fontSize = `${fontSize * 0.53125}px`; // 0.85rem

      // Update text content
      document.getElementById('brandName').textContent = config.brand_name || defaultConfig.brand_name;
      document.getElementById('mainHeadline').textContent = config.main_headline || defaultConfig.main_headline;
      document.getElementById('subHeadline').textContent = config.sub_headline || defaultConfig.sub_headline;
      document.getElementById('bgYear').textContent = config.background_year || defaultConfig.background_year;
      document.getElementById('campaignTagline').textContent = config.campaign_tagline || defaultConfig.campaign_tagline;
      document.getElementById('yearDisplay').textContent = config.year || defaultConfig.year;
      document.getElementById('engagementCount').textContent = config.engagement_count || defaultConfig.engagement_count;
      document.getElementById('discountText').textContent = config.discount_text || defaultConfig.discount_text;
      document.getElementById('ctaText').textContent = config.cta_button || defaultConfig.cta_button;
    }

    function adjustBrightness(color, percent) {
      const num = parseInt(color.replace("#",""), 16);
      const amt = Math.round(2.55 * percent);
      const R = (num >> 16) + amt;
      const G = (num >> 8 & 0x00FF) + amt;
      const B = (num & 0x0000FF) + amt;
      return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 +
        (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255))
        .toString(16).slice(1);
    }

    if (window.elementSdk) {
      window.elementSdk.init({
        defaultConfig,
        onConfigChange,
        mapToCapabilities: (config) => ({
          recolorables: [
            {
              get: () => config.background_color || defaultConfig.background_color,
              set: (value) => {
                config.background_color = value;
                window.elementSdk.setConfig({ background_color: value });
              }
            },
            {
              get: () => config.surface_color || defaultConfig.surface_color,
              set: (value) => {
                config.surface_color = value;
                window.elementSdk.setConfig({ surface_color: value });
              }
            },
            {
              get: () => config.text_color || defaultConfig.text_color,
              set: (value) => {
                config.text_color = value;
                window.elementSdk.setConfig({ text_color: value });
              }
            },
            {
              get: () => config.button_color || defaultConfig.button_color,
              set: (value) => {
                config.button_color = value;
                window.elementSdk.setConfig({ button_color: value });
              }
            },
            {
              get: () => config.accent_color || defaultConfig.accent_color,
              set: (value) => {
                config.accent_color = value;
                window.elementSdk.setConfig({ accent_color: value });
              }
            }
          ],
          borderables: [],
          fontEditable: {
            get: () => config.font_family || defaultConfig.font_family,
            set: (value) => {
              config.font_family = value;
              window.elementSdk.setConfig({ font_family: value });
            }
          },
          fontSizeable: {
            get: () => config.font_size || defaultConfig.font_size,
            set: (value) => {
              config.font_size = value;
              window.elementSdk.setConfig({ font_size: value });
            }
          }
        }),
        mapToEditPanelValues: (config) => new Map([
          ["brand_name", config.brand_name || defaultConfig.brand_name],
          ["main_headline", config.main_headline || defaultConfig.main_headline],
          ["sub_headline", config.sub_headline || defaultConfig.sub_headline],
          ["background_year", config.background_year || defaultConfig.background_year],
          ["campaign_tagline", config.campaign_tagline || defaultConfig.campaign_tagline],
          ["year", config.year || defaultConfig.year],
          ["engagement_count", config.engagement_count || defaultConfig.engagement_count],
          ["discount_text", config.discount_text || defaultConfig.discount_text],
          ["cta_button", config.cta_button || defaultConfig.cta_button]
        ])
      });
    }
  </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9a3fdb3a52e1d5ff',t:'MTc2NDA2MDE4NC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>