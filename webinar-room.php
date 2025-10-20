<?php
session_start();
 date_default_timezone_set("UTC"); // Base neutral - cada prospecto usa su timezone guardado
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/admin/includes/db.php';

/* ====== Ventana de acceso por horario (2 minutos antes) ====== */
$emailParam    = trim(strtolower($_GET['email'] ?? ($_SESSION['prospect_email'] ?? '')));
$EARLY_WINDOW  = 120; // segundos

$tooEarly        = false;
$secondsToGo     = null;
$nombreProspect  = '';
$horarioWebinar  = null;

if ($emailParam) {
  // Usamos mysqli ($conn) según tu include
  $stmt = $conn->prepare("SELECT nombre, horario_webinar FROM prospect WHERE email=? LIMIT 1");
  $stmt->bind_param("s", $emailParam);
  $stmt->execute();
  $stmt->bind_result($nombreProspect, $horarioWebinar);
  if ($stmt->fetch() && !empty($horarioWebinar)) {
    $start = new DateTime($horarioWebinar, new DateTimeZone('America/Puerto_Rico'));
    $now   = new DateTime('now',          new DateTimeZone('America/Puerto_Rico'));
    $secondsToGo = $start->getTimestamp() - $now->getTimestamp();

    if ($secondsToGo > $EARLY_WINDOW) {
      $tooEarly = true;
    } else {
      $_SESSION['from_schedule'] = true;
    }
  }
  $stmt->close();
}

/* ====== Página de espera con countdown ====== */
if ($tooEarly) {
  $mins = max(0, floor($secondsToGo / 60));
  $secs = max(0, $secondsToGo % 60);
  ?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Esperando tu horario – IngresosAI</title>
    <style>
      body{background:#0d2c54;color:#fff;font-family:Poppins,Arial,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
      .wrap{text-align:center;max-width:560px;padding:20px}
      .count{font-size:3rem;color:#ffcc00;margin:20px 0}
      .btn{background:#ffcc00;color:#0d2c54;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:700}
    </style>
  




<style id="attendeeStylesV6">
.att-badge{display:inline-flex;align-items:center;font-weight:700;margin-left:12px}
.att-badge .num{color:#e03131}
.att-hide{display:none!important}
</style>
</head>
  <body>
    <div class="wrap">
      <img src="https://ingresosai.info/robot-ingresosai.png" width="180" alt="IngresosAI">
      <h2>⏳ Falta poco para empezar</h2>
      <p>Tu acceso se habilitará <b>2 minutos antes</b> del horario reservado.</p>
      <div id="cd" class="count"><?php echo sprintf('%02d:%02d', $mins, $secs); ?></div>
      <a href="webinar-schedule.php?name=<?php echo urlencode($nombreProspect ?? ''); ?>&email=<?php echo urlencode($emailParam); ?>" class="btn">Cambiar horario</a>
    </div>
    <script>
      let remaining = <?php echo (int)$secondsToGo; ?>;
      const EARLY_WINDOW = <?php echo (int)$EARLY_WINDOW; ?>;
      const cd = document.getElementById('cd');
      function tick(){
        remaining--;
        if (remaining <= EARLY_WINDOW) { window.location.reload(); return; }
        const m = Math.floor(remaining/60), s = remaining%60;
        cd.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
      }
      setInterval(tick,1000);
    </script>
  <script src="/assets/js/attendees.js?v=1"></script>
<script src="/assets/js/attendees.js?v=1"></script>
<script src="/assets/js/attendees.js?v=2"></script>
<script src="/assets/js/attendees.js?v=4"></script>
</body>
  </html>
  <?php exit;
}

/* ====== Protección de acceso ====== */
if (
  !isset($_SESSION['from_schedule']) &&
  (!isset($_GET['debug']) || $_GET['debug'] !== 'alaviles123')
) {
  echo "<!DOCTYPE html><html><body><h2 style='color:red;text-align:center'>Acceso no autorizado</h2><script src="/assets/js/attendees.js?v=4"></script>
</body></html>";
  exit;
}

// Exponemos un posible lead_id (puede ser email o id)
$leadIdForJs = $_SESSION['prospect_id'] ?? $_SESSION['prospect_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Webinar Exclusivo IngresosAI – Domina CPA con IA</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<style>
  :root{--brand:#0d2c54;--brand-2:#00b8ff;--bg:#f4f6f9;--ink:#0f172a}
  body{background:var(--bg);font-family:Poppins,Arial,sans-serif}
  .header{background:var(--brand);color:#fff;padding:14px 10px;text-align:center;border-bottom:3px solid var(--brand-2)}
  .brand-row img{width:160px;max-width:48vw;height:auto;object-fit:contain}

  .video-container{position:relative;width:100%;padding-top:56.25%;background:#000;border:3px solid var(--brand);border-radius:12px;overflow:hidden}
  video,img.thumb{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  #unmuteBtn{position:absolute;bottom:14px;right:14px;background:rgba(0,0,0,.64);color:#fff;border:none;padding:10px 14px;border-radius:26px}

  .chat-box{background:#fff;border:1px solid #e7eaf0;border-radius:12px;padding:12px;height:480px;overflow-y:auto;}
  .msg{margin-bottom:10px}
  .bubble{background:#f5f7fb;color:var(--ink);border-radius:12px;padding:10px 12px;display:inline-block;max-width:100%}
  .msg.me .bubble{background:var(--brand);color:#fff}

  .poll-card{background:#fff;border:1px solid #ddd;border-radius:12px;margin-top:15px;padding:15px;text-align:center}
  .poll-title{font-weight:700;margin-bottom:8px;color:var(--brand)}
  .poll-options{display:flex;justify-content:center;align-items:center;flex-wrap:wrap;gap:8px}
  .poll-btn{background:var(--brand);color:#fff;border:none;border-radius:50px;padding:8px 14px;font-size:14px;font-weight:600;cursor:pointer}
  .poll-btn[disabled]{opacity:.6;cursor:not-allowed}
  .poll-btn:hover{opacity:.9}
  .poll-meta{font-size:12px;opacity:.7;margin-top:8px}
  .poll-result{margin-top:10px;text-align:left}
  .bar{height:10px;border-radius:6px;background:#e9eef6;overflow:hidden}
  .fill{height:10px;background:var(--brand);border-radius:6px;transition:width .35s ease}
</style>
</head>
<body>
  <div class="header">
    <div class="brand-row">
      <img src="https://ingresosai.info/robot-ingresosai.png" alt="IngresosAI" />
    </div>
    <h1 style="font-size:1.4rem;font-weight:700;margin:6px 0 0;">🎥 Webinar Exclusivo <span style="color:#00b8ff;">IngresosAI</span></h1>
    <p style="font-size:.98rem;margin:2px 0 6px;color:#d1e9ff;">Domina CPA Marketing con Inteligencia Artificial</p>
  </div>

  <div class="container mt-4">
    <div class="row g-4">
      <!-- Video -->
      <div class="col-md-8">
        <div class="video-container">
          <img class="thumb" id="thumb1" src="thumbnail1.jpg" alt="Conexión al webinar" />
          <video id="webinarVideo" playsinline muted style="display:none;"></video>
          <button id="unmuteBtn" style="display:none;">🔊 Activar sonido</button>
        </div>

        <!-- Polls -->
        <div id="pollContainer"></div>
      </div>

      <!-- Chat -->
      <div class="col-md-4">
        <h5>💬 Chat en Vivo</h5>
        <div id="chat" class="chat-box"></div>

        <div class="input-group mt-2">
          <input type="text" id="msg" class="form-control" placeholder="Escribe tu pregunta…" />
          <button id="sendBtn" class="btn btn-primary" onclick="sendMessage()">Enviar</button>
        </div>

        <p id="attendees" class="text-center mt-3 fw-bold" style="color:var(--brand);">
          👥 Asistentes conectados: <span id="count">190</span> — <span id="inRoom">190 en sala</span>
        </p>
      </div>
    </div>
  </div>

<script>
// Exponemos lead_id al JS
window.LEAD_ID = <?php echo json_encode($leadIdForJs); ?>;

window.addEventListener("load", () => {
  /* ---------- VIDEO (HLS) ---------- */
  const video     = document.getElementById("webinarVideo");
  const thumb1    = document.getElementById("thumb1");
  const unmuteBtn = document.getElementById("unmuteBtn");
  const qs        = new URLSearchParams(location.search);
  const STREAM    = qs.get("stream") || "https://ingresosai.info/hls/webinar.m3u8";

  function startVideo(){
    thumb1.style.display = "none";
    video.style.display  = "block";
    unmuteBtn.style.display = "block";

    if (window.Hls && Hls.isSupported()){
      const hls = new Hls();
      hls.loadSource(STREAM);
      hls.attachMedia(video);
      hls.on(Hls.Events.MANIFEST_PARSED, () => video.play().catch(()=>{}));
    } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
      video.src = STREAM; video.play().catch(()=>{});
    } else {
      video.src = STREAM; video.play().catch(()=>{});
    }
  }
  unmuteBtn.addEventListener("click", () => { video.muted=false; video.volume=1; unmuteBtn.style.display="none"; });
  setTimeout(startVideo, 800);

  /* ---------- Helpers de UI / tiempo humano ---------- */
  const chat     = document.getElementById("chat");
  const msgInput = document.getElementById("msg");
  const sendBtn  = document.getElementById("sendBtn");

  const wait = (ms)=>new Promise(r=>setTimeout(r,ms));

  function addBubble(role,name,html){
    const row = document.createElement("div");
    row.className = `msg ${role==='me'?'me':'them'}`;
    row.innerHTML = `<div class="bubble"><b>${name}:</b> ${html}</div>`;
    chat.appendChild(row);
    chat.scrollTop = chat.scrollHeight;
  }
  function showTyping(){
    if (document.getElementById("typingRow")) return;
    const row=document.createElement("div");
    row.id="typingRow";
    row.className="msg them";
    row.innerHTML=`<div class="bubble"><i>Adriel está escribiendo…</i></div>`;
    chat.appendChild(row);
    chat.scrollTop=chat.scrollHeight;
  }
  function hideTyping(){
    const row=document.getElementById("typingRow");
    if(row) row.remove();
  }

  /**
   * Orquesta la respuesta "humana":
   * - Espera ~2s antes de empezar a escribir
   * - Muestra "escribiendo…" y mantiene un mínimo de tiempo de tipeo
   * - Publica la respuesta cuando el fetch (si lo hay) termine y se cumpla el tiempo mínimo
   */
  async function respondWithHumanTiming(getAnswerFn, { who='', question='', minPreDelay=2000, preJitter=600, minTyping=1800, maxTyping=3200, fallback='' } = {}){
    const preDelay = minPreDelay + Math.floor(Math.random()*preJitter);
    await wait(preDelay);         // ⏱️ pausa inicial antes de tipear
    showTyping();
    const typingMs = minTyping + Math.floor(Math.random()*(maxTyping - minTyping));

    let payload = null;
    try{
      const fetchPromise = getAnswerFn ? getAnswerFn() : Promise.resolve(null);
      const [data] = await Promise.all([
        fetchPromise.catch(()=>null),
        wait(typingMs)            // ⌨️ tiempo de tipeo mínimo
      ]);
      payload = data;
    }catch(e){
      payload = null;
    }
    hideTyping();

    const namePart = who ? `, ${who.split(' ')[0]}` : '';
    const text = (payload && payload.text) ? payload.text :
                 (fallback || `¡Gracias por tu comentario${namePart}! Ahora mismo ampliamos ese punto 👌`);

    addBubble('them','Adriel (Moderador)', text);
  }

  /* ---------- CHAT (usuario -> Adriel) ---------- */
  window.sendMessage = async function(){
    const q = msgInput.value.trim();
    if(!q || sendBtn.disabled) return;
    sendBtn.disabled = true;
    addBubble('me','Tú',q);
    msgInput.value='';

    const getAnswer = async () => {
      const res = await fetch('chatResponder.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ question:q })
      });
      return await res.json();
    };

    await respondWithHumanTiming(getAnswer, {
      who: 'Tú',
      question: q,
      minPreDelay: 2000,      // ~2s antes de empezar a escribir
      preJitter: 800,         // +0–0.8s aleatorio
      minTyping: 1800,        // 1.8s mínimo de tipeo
      maxTyping: 3200,
      fallback: `¡Buena pregunta! Te lo explico: ${q}`
    });

    sendBtn.disabled=false;
    msgInput.focus();
  };
  msgInput.addEventListener('keydown',(e)=>{ if(e.key==='Enter'){ e.preventDefault(); sendMessage(); }});

  // Mensajes iniciales para no arrancar vacío (sin cambios de timing)
  setTimeout(()=> addBubble('them','Adriel (Moderador)','¡Bienvenido/a! Dime desde qué país te conectas y qué esperas aprender hoy 🙌'), 1200);
  setTimeout(()=> addBubble('them','Adriel (Moderador)','Durante el webinar voy dejando links y encuestas para ayudarte 😉'), 3000);

  /* ---------- SIMULACIÓN DE ASISTENTES (misma lógica) ---------- */
  const countEl  = document.getElementById("count");
  const inRoomEl = document.getElementById("inRoom");
  let count = parseInt(countEl?.textContent || "190", 10) || 190;

  setInterval(() => {
    const change = Math.floor(Math.random() * 4) - 2; // -2 a +1
    count = Math.max(150, count + change);
    if (countEl)  countEl.textContent  = String(count);
    if (inRoomEl) inRoomEl.textContent = `${count} en sala`;
  }, 5000);

  /* ---------- RESPUESTA AUTOMÁTICA DE ADRIEL A MENSAJES DEL TIMELINE ---------- */
  function stripHtml(s){ return s.replace(/<[^>]*>/g,'').trim(); }

  function parseNameAndText(raw){
    // "<b>Adriel (Moderador):</b> texto"
    if (/^<b>\s*Adriel\s*\(Moderador\)\s*:\s*<\/b>/i.test(raw)){
      return { fromAdriel:true, name:'Adriel (Moderador)', text: raw.replace(/^<b>\s*Adriel\s*\(Moderador\)\s*:\s*<\/b>\s*/i,'') };
    }
    // "Adriel (Moderador): texto" sin <b>
    if (/^\s*Adriel\s*\(Moderador\)\s*:/i.test(stripHtml(raw))){
      return { fromAdriel:true, name:'Adriel (Moderador)', text: raw.replace(/^\s*Adriel\s*\(Moderador\)\s*:\s*/i,'') };
    }
    // "Nombre: texto"
    const m = stripHtml(raw).match(/^([^:]+):\s*(.*)$/);
    if (m) return { name:m[1].trim(), text: raw.replace(/^[^:]+:\s*/,'') };
    return { name:'Invitado', text: raw };
  }

  function scheduleAdrielIfQuestion(text, who=''){
    const q = stripHtml(text);
    if (!/[?¿]/.test(q)) return; // Solo responde si detecta pregunta

    const getAnswer = async () => {
      const res = await fetch('chatResponder.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ question: q, from: who })
      });
      return await res.json();
    };

    // Ahora Adriel tarda ~2s en empezar a escribir + 1.8–3.2s de tipeo
    respondWithHumanTiming(getAnswer, {
      who,
      question: q,
      minPreDelay: 2000,
      preJitter: 800,
      minTyping: 1800,
      maxTyping: 3200,
      fallback: `¡Excelente pregunta${who?`, ${who.split(' ')[0]}`:''}! Te lo explico: ${q}`
    });
  }

  // Muestra el mensaje del timeline y dispara respuesta de Adriel si aplica
  function addChatMessage(raw){
    const parsed = parseNameAndText(raw);

    if (parsed.fromAdriel){
      addBubble('them', 'Adriel (Moderador)', parsed.text);
      return;
    }

    const guestName = parsed.name || 'Invitado';
    addBubble('them', guestName, parsed.text);
    scheduleAdrielIfQuestion(parsed.text, guestName);
  }

  /* ---------- TIMELINE (60 MENSAJES, intacto) ---------- */
  const timelineMsgs = [
    // 👇 Conversaciones naturales entre Adriel y asistentes
    { time: 65,  msg: "<i>Adriel está escribiendo...</i>" },
    { time: 67,  msg: "<b>Adriel (Moderador):</b> ¡Así es Luis! CPA es 100% real 💪 lo importante es hacerlo con estrategia." },
    { time: 97,  msg: "<i>Adriel está escribiendo...</i>" },
    { time: 99,  msg: "<b>Adriel (Moderador):</b> Bienvenida Sofía 🙌, prepárate, vas a entender todo paso a paso." },
    { time: 156, msg: "Pedro 🇵🇷: Qué bueno saberlo 🔥" },
    { time: 158, msg: "<b>Adriel (Moderador):</b> Gracias Pedro 👏, Puerto Rico siempre presente 🇵🇷" },
    { time: 216, msg: "Miguel 🇪🇸: ¡Eso tiene sentido!" },
    { time: 218, msg: "<b>Adriel (Moderador):</b> Exacto Miguel, por eso explicamos cómo ganar sin depender de suerte 😎" },
    { time: 376, msg: "Patricio 🇨🇱: Jajaja, ya me convencieron 😂" },
    { time: 378, msg: "<b>Adriel (Moderador):</b> Jajaja, me alegra Patricio 🤝, ¡vas a ver que es posible!" },
    { time: 456, msg: "Juan 🇨🇴: Buen dato eso de los tiempos 👌" },
    { time: 458, msg: "<b>Adriel (Moderador):</b> Correcto Juan, lo normal son 24–48 h para aprobación 🚀" },
    { time: 575, msg: "<i>Adriel está escribiendo...</i>" },
    { time: 577, msg: "<b>Adriel (Moderador):</b> Roberto, no te preocupes 🙌 muchos comienzan desde cero y avanzan rápido." },
    { time: 725, msg: "Ricardo 🇨🇱: Lo anoto, me gusta esa dupla 👏" },
    { time: 727, msg: "<b>Adriel (Moderador):</b> ¡Excelente Ricardo! ZeroPark + OddBytes = 🔥 resultados reales." },
    { time: 905, msg: "Teresa 🇨🇱: Tiene lógica eso de optimizar 💡" },
    { time: 907, msg: "<b>Adriel (Moderador):</b> Exactamente Teresa, la optimización es lo que separa al que gana del que gasta 😉" },
    { time: 981, msg: "Graciela 🇦🇷: Gracias por aclararlo 🙏" },
    { time: 983, msg: "<b>Adriel (Moderador):</b> Un gusto Graciela 😊, todo lo que mostramos son datos reales de alumnos." },
    { time: 1205, msg: "<i>Adriel está escribiendo...</i>" },
    { time: 1207, msg: "<b>Adriel (Moderador):</b> Gracias Silvia 🙏, Al lleva años enseñando y aplicando esto a gran escala." },
    { time: 1585, msg: "Daniel 🇨🇱: ¡Eso me motiva!" },
    { time: 1587, msg: "<b>Adriel (Moderador):</b> Tal cual Daniel 💪, la constancia paga siempre." },
    { time: 1765, msg: "Lucía 🇩🇴: Qué historia más inspiradora 👏" },
    { time: 1767, msg: "<b>Adriel (Moderador):</b> Gracias Lucía 😍, el caso senior fue uno de mis favoritos." },
    { time: 1885, msg: "Esteban 🇵🇷: Increíble, la IA ya lo domina todo" },
    { time: 1887, msg: "<b>Adriel (Moderador):</b> Totalmente Esteban 🤖, la IA es nuestra aliada para escalar campañas." },
    { time: 2105, msg: "Pedro 🇲🇽: ¡Perfecto, espero el final entonces!" },
    { time: 2107, msg: "<b>Adriel (Moderador):</b> Claro Pedro 😎, te va a encantar la parte final donde explicamos todo el plan." },
    { time: 40, msg: "Luis 🇨🇴: ¿Esto de CPA es real? 🤔" },
    { time: 60, msg: "María 🇨🇱: Sí funciona, yo conozco casos 👏" },
    { time: 90, msg: "Pedro 🇵🇷: ¡Saludos desde Puerto Rico!" },
    { time: 120, msg: "Sofía 🇦🇷: Primera vez en un webinar de este tema 🙌" },
    { time: 150, msg: "José 🇲🇽: Buena introducción 👏" },
    { time: 180, msg: "Claudia 🇩🇴: Estoy aquí porque quiero independencia financiera" },
    { time: 210, msg: "Miguel 🇪🇸: CPA lo había escuchado pero nunca entendí, gracias por explicar!" },
    { time: 250, msg: "Raúl 🇨🇷: Qué bien explicado 🔥" },
    { time: 280, msg: "Ana 🇨🇺: Yo soy nueva en esto y quiero aprender 🙏" },
    { time: 310, msg: "Lucía 🇨🇱: Muy claro lo de reputación y metas escalonadas" },
    { time: 340, msg: "Patricio 🇨🇱: Suena interesante, pero ¿será tan fácil? 🤨" },
    { time: 370, msg: "Alba 🇲🇽: Tengo dudas pero estoy escuchando atenta 👂" },
    { time: 400, msg: "Carlos 🇲🇽: Estoy motivado 💪" },
    { time: 450, msg: "Juan 🇨🇴: ¿Cuánto se tarda en aprobar una oferta? 🤔" },
    { time: 480, msg: "Marta 🇪🇸: Eso de metas escalonadas me gusta mucho 🙌" },
    { time: 510, msg: "Diego 🇦🇷: Estoy tomando notas, esto vale oro 📒" },
    { time: 540, msg: "Carmen 🇵🇷: Siempre había querido entender bien CPA y ahora sí 😍" },
    { time: 570, msg: "Roberto 🇲🇽: ¿Y si no tengo experiencia previa? 🤷‍♂️" },
    { time: 600, msg: "Lucía 🇩🇴: Excelente lo de Esencial y Máxima Rentabilidad 👏" },
    { time: 640, msg: "David 🇦🇷: ¡Ya quiero aplicar estas campañas!" },
    { time: 680, msg: "Andrea 🇲🇽: Estoy en shock con la claridad de esto" },
    { time: 720, msg: "Ricardo 🇨🇱: Tremendo lo de ZeroPark y OddBytes 🚀" },
    { time: 760, msg: "Isabel 🇺🇸: Esto está muy pro, nunca había visto algo así en español 👏" },
    { time: 800, msg: "Manuel 🇩🇴: La IA está cambiando todo 🤖" },
    { time: 850, msg: "Alejandro 🇪🇸: Súper lo del control de riesgo 💡" },
    { time: 900, msg: "Teresa 🇨🇱: Eso de pausar targets malos es clave 🔑" },
    { time: 940, msg: "Juan 🇲🇽: Me impresiona lo de los $35,000 de algunos alumnos 😱" },
    { time: 980, msg: "Graciela 🇦🇷: ¿De verdad alguien gana tanto con CPA? 😬" },
    { time: 1000, msg: "Patricia 🇦🇷: Me da confianza ver casos de alumnos 🙌" },
    { time: 1060, msg: "Diego 🇨🇴: ¡Gracias por mostrar resultados reales!" },
    { time: 1100, msg: "Laura 🇲🇽: Estoy convencida 🚀" },
    { time: 1160, msg: "Fernando 🇵🇷: Estoy listo para arrancar hoy mismo 🔥" },
    { time: 1200, msg: "Silvia 🇪🇸: Gracias Al, se nota tu experiencia 🙏" },
    { time: 1260, msg: "Roberto 🇲🇽: Estoy motivadísimo 💪" },
    { time: 1320, msg: "Clara 🇨🇱: Me cuesta creerlo pero suena bien explicado" },
    { time: 1400, msg: "Carmen 🇩🇴: Esto de optimización con IA me voló la cabeza" },
    { time: 1460, msg: "Luis 🇦🇷: Nunca había visto algo tan organizado" },
    { time: 1520, msg: "Mónica 🇲🇽: Se nota que esto está probado 💡" },
    { time: 1580, msg: "Daniel 🇨🇱: Estoy viendo que esto sí es real" },
    { time: 1640, msg: "Álvaro 🇪🇸: Esto está brutal 🚀" },
    { time: 1700, msg: "Julia 🇨🇴: Me encanta que hay comunidad 👥" },
    { time: 1760, msg: "Lucía 🇩🇴: Increíble lo del caso senior 👏" },
    { time: 1820, msg: "Marcos 🇲🇽: Esto me da esperanza 🙌" },
    { time: 1880, msg: "Esteban 🇵🇷: La IA es el futuro y aquí ya está aplicado" },
    { time: 1940, msg: "Sara 🇨🇱: Estoy tomando nota 📘" },
    { time: 2000, msg: "Pablo 🇪🇸: Muy bueno lo de orquestación IA" },
    { time: 2060, msg: "Sonia 🇨🇴: Brutal 🔥" },
    { time: 2100, msg: "Pedro 🇲🇽: Aunque aún dudo, cada vez me convence más 🤔" },
    { time: 3100, msg: "<b>Adriel (Moderador):</b> Ahora comparto los planes 👇" },
    { time: 3120, msg: "👉 <a href='https://ingresosai.com/offers/kuEqXGCV/checkout' class='offer-link' target='_blank'>Plan Esencial – $1,500</a>" },
    { time: 3140, msg: "👉 <a href='https://ingresosai.com/offers/AF2pfXtn/checkout' class='offer-link' target='_blank'>Máxima Rentabilidad – $2,500</a>" },
    { time: 3160, msg: "👉 <a href='https://ingresosai.com/offers/M4tdE78z/checkout' class='offer-link' target='_blank'>Pack Premium + Coaching – $5,000</a>" },
    { time: 3180, msg: "Lucía 🇩🇴: ¡Ya me inscribí al Esencial! 🎉" },
    { time: 3200, msg: "Pedro 🇵🇷: Yo voy por el de $2,500 💪" },
    { time: 3220, msg: "Carla 🇲🇽: Soy alumna oficial de IngresosAI 🙌" },
    { time: 3240, msg: "Jorge 🇨🇴: Estoy pidiendo un préstamo pero entro sí o sí 🙏" },
    { time: 3260, msg: "Ana 🇦🇷: ¡Ya entré al de $5,000! 🔥" },
    { time: 3280, msg: "Luis 🇲🇽: Felicidades a todos los que se inscribieron 👏" },
    { time: 3300, msg: "Marta 🇪🇸: ¡Estoy dentro, gracias Al! 🙌" },
    { time: 3320, msg: "Sofía 🇨🇱: Qué emoción empezar hoy 🚀" },
    { time: 3340, msg: "Diego 🇵🇷: Esto es lo mejor que he hecho 🙌" },
    { time: 3360, msg: "Claudia 🇨🇴: ¡Feliz de empezar en IngresosAI! 🎉" }
  ];
  timelineMsgs.forEach(item => setTimeout(() => { addChatMessage(item.msg); }, item.time * 1000));

  /* ---------- POLLS (clicables + resultados + persistencia local + envío a backend) ---------- */
  const pollContainer = document.getElementById("pollContainer");
  const pollSchedule = [
    { time:  45000, id: "poll1", title: "¿Desde dónde nos ves?",        options: ["México/Latam","EE.UU./Canadá","Europa","Otro"] },
    { time: 210000, id: "poll2", title: "Tu experiencia con CPA",       options: ["Cero experiencia","Sé lo básico","Ya he ganado $$"] },
    { time: 540000, id: "poll3", title: "Meta de ingresos mensuales",   options: ["$500–$1,000","$1,000–$3,000","$3,000+"] }
  ];

  const LS_KEY = (id) => `poll_${id}_data`;

  function getPollData(id){
    try { return JSON.parse(localStorage.getItem(LS_KEY(id)) || 'null'); }
    catch(e){ return null; }
  }
  function savePollData(id, data){
    localStorage.setItem(LS_KEY(id), JSON.stringify(data));
  }

  function pickBaseTotals(n){
    // 20–60 cada uno (aleatorio), luego se ajustan con tu voto
    return Array.from({length:n}, () => Math.floor(20 + Math.random()*41));
  }

  async function postPollToBackend(poll_id, question, answer){
    try{
      await fetch('/admin/polls-responses.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          poll_id: poll_id,
          question: question,
          answer: answer,
          lead_id: (window.LEAD_ID || null)
        })
      });
    }catch(e){
      // Silencioso: persistencia local ya aplicada
      console.warn('No se pudo enviar el poll al backend:', e);
    }
  }

  function renderPoll(p){
    const existing = document.getElementById(p.id);
    if (existing) existing.remove();

    const card = document.createElement("div");
    card.className = "poll-card";
    card.id = p.id;

    const data = getPollData(p.id);

    if (!data || typeof data.selected !== 'number' || !Array.isArray(data.totals)) {
      // Aún no ha votado ⇒ mostrar opciones
      card.innerHTML = `
        <div class="poll-title">${p.title}</div>
        <div class="poll-options">
          ${p.options.map((o,i)=>`<button class="poll-btn" data-idx="${i}">${o}</button>`).join('')}
        </div>
        <div class="poll-meta">Responde para ver resultados en tiempo real</div>
      `;
      // Listeners de voto
      card.querySelectorAll(".poll-btn").forEach(btn=>{
        btn.addEventListener("click", async ()=>{
          if (btn.disabled) return; // antirebote
          // Deshabilitar todos mientras procesa
          card.querySelectorAll(".poll-btn").forEach(b=>b.disabled=true);

          const idx = parseInt(btn.getAttribute("data-idx"),10);
          const base = pickBaseTotals(p.options.length);
          base[idx] += 15 + Math.floor(Math.random()*10); // ventaja a tu voto
          savePollData(p.id, { selected: idx, totals: base });

          // Enviar al backend (no bloquea UI)
          postPollToBackend(p.id, p.title, p.options[idx]);

          showResults(p, idx, base);
        });
      });
    } else {
      // Ya votó ⇒ mostrar resultados usando sus totales guardados
      showResults(p, data.selected, data.totals, card);
    }

    pollContainer.appendChild(card);
  }

  function showResults(p, myIdx, totals, cardEl){
    const sum = totals.reduce((a,b)=>a+b,0);
    const htmlResults = p.options.map((opt,i)=>{
      const pct = Math.round((totals[i]/sum)*100);
      return `
        <div style="margin:6px 0 10px">
          <div style="display:flex;justify-content:space-between;font-size:13px">
            <span>${opt}${i===myIdx ? " • Tu voto" : ""}</span>
            <span>${pct}%</span>
          </div>
          <div class="bar"><div class="fill" style="width:${pct}%"></div></div>
        </div>
      `;
    }).join("");

    const card = cardEl || document.getElementById(p.id);
    card.innerHTML = `
      <div class="poll-title">${p.title}</div>
      <div class="poll-result">${htmlResults}</div>
      <div class="poll-meta">Total respuestas: ${sum.toLocaleString()}</div>
    `;
  }

  // Programar aparición de encuestas
  pollSchedule.forEach(p => setTimeout(()=>renderPoll(p), p.time));
});
</script>
<script src="/assets/js/attendees.js?v=4"></script>
</body>
</html>



||gis' -i "$FILE"
perl -0777 -pe 's|<script[^>]*id="attendeeCounterV3[^"]*"[^>]*>.*?</script>||gis' -i "$FILE"
perl -0777 -pe 's|<script[^>]*id="attendeeCounterV2[^"]*"[^>]*>.*?</script>||gis' -i "$FILE"
perl -0777 -pe 's|<style[^>]*id="attendeeStyles[^"]*"[^>]*>.*?</style>||gis' -i "$FILE"

# 3) CSS mínimo (números rojos, oculta duplicados)
perl -0777 -pe 's%</head>%
</head>%s' -i "$FILE"

# 4) Script persistente con MutationObserver + setInterval
cat >> "$FILE" <<'JS'


<script id="attendeeCounterV6">
(function(){
  const CAP = 150, TARGET = 120;
  let count = 34;

  const q = (sel,ctx=document)=>ctx.querySelector(sel);
  const qa = (sel,ctx=document)=>Array.from(ctx.querySelectorAll(sel));
  const hasTxt = (el, s) => el && (el.textContent||'').toLowerCase().includes(s.toLowerCase());

  // Encuentra el panel del chat a partir del input "Escribe tu pregunta..."
  function findChatPanel(){
    const input = q('input[placeholder*="Escribe tu pregunta"]');
    if(!input) return null;
    // Subimos hasta un contenedor que contenga el header H3 y la zona de mensajes
    let p = input.parentElement;
    for(let i=0;i<8 && p;i++){ // sube pocos niveles para no llegar a body
      if(p.querySelector('h3') && p.querySelector('input[placeholder*="Escribe tu pregunta"]')) return p;
      p = p.parentElement;
    }
    return input.closest('div');
  }

  function findHeader(panel){
    if(!panel) return null;
    // Busca un H3 que contenga "Chat en Vivo"
    const h3s = qa('h3', panel);
    let title = h3s.find(h => hasTxt(h,'chat en vivo')) || h3s[0];
    if(!title) return null;
    // Usamos el contenedor del título como header
    let header = title.parentElement || title;
    header.style.display = 'flex';
    header.style.alignItems = 'center';
    header.style.justifyContent = 'space-between';
    return header;
  }

  function ensureBadge(header){
    let b = q('#attendeeBadgeHeader');
    if(!b){
      b = document.createElement('span');
      b.id = 'attendeeBadgeHeader';
      b.className = 'att-badge';
      header.appendChild(b);
    }
    return b;
  }

  function fmt(n){
    return '👥 Asistentes conectados en sala: <span class="num">'+n+
           '</span>/<span class="num">'+CAP+'</span>';
  }

  function hideFooterLine(panel){
    if(!panel) return;
    // Oculta cualquier línea que diga "Asistentes conectados:"
    qa('div,span,p,small,footer,section', panel)
      .filter(el => hasTxt(el,'asistentes conectados:'))
      .forEach(el => el.classList.add('att-hide'));
  }

  function maybeSyncFromFooter(panel){
    if(!panel) return;
    const line = qa('div,span,p,small,footer,section', panel)
      .find(el => hasTxt(el,'asistentes conectados:'));
    if(!line) return;
    const m = (line.textContent||'').match(/\d+/g);
    if(m && m[0]){
      const n = parseInt(m[0],10);
      if(!isNaN(n)) count = Math.min(Math.max(n,34), TARGET);
    }
  }

  function place(){
    const panel  = findChatPanel();
    const header = findHeader(panel);
    if(!panel || !header) return false;

    hideFooterLine(panel);
    maybeSyncFromFooter(panel);

    const badge = ensureBadge(header);
    badge.innerHTML = fmt(count);
    return true;
  }

  function tick(){
    if(count < TARGET){
      count = Math.min(TARGET, count + Math.max(1, Math.round(Math.random()*3)));
      const b = q('#attendeeBadgeHeader');
      if(b) b.innerHTML = fmt(count);
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    // Intentos periódicos + observador del DOM
    place();
    const obs = new MutationObserver(place);
    obs.observe(document.body, {childList:true, subtree:true});
    setInterval(function(){ place(); tick(); }, 2000);
  });
})();
</script>
