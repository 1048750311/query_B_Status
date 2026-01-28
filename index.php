<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ステータスがBのユーザー検索</title>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    body{font-family:system-ui,Segoe UI,Arial;background:#f6f7fb;margin:0;padding:20px;}
    .card{max-width:820px;margin:0 auto;background:#fff;border:1px solid #e7e7e7;border-radius:12px;padding:16px;}
    .row{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    label{font-size:12px;color:#6b7280;display:block;margin-bottom:6px;}
    input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;min-width:200px;}
    button{padding:10px 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer;}
    .muted{color:#6b7280;font-size:12px;}
    table{width:100%;border-collapse:collapse;}
    th,td{border-bottom:1px solid #eee;padding:8px;text-align:left;}
  </style>
</head>
<body>

<div class="card">

  <!-- language switch -->
  <div style="text-align:right;margin-bottom:10px;">
    <button onclick="setLang('zh')">中文</button>
    <button onclick="setLang('ja')">日本語</button>
    <button onclick="setLang('en')">English</button>
  </div>

  <h2 id="title"></h2>
  <div class="muted" id="dateFormat"></div>

  <div class="row" style="margin-top:14px;">
    <div>
      <label id="labelStart"></label>
      <input id="start" type="date">
    </div>
    <div>
      <label id="labelEnd"></label>
      <input id="end" type="date">
    </div>
    <div>
      <button id="btn"></button>
    </div>
  </div>

  <div id="result" style="margin-top:16px;"></div>
</div>

<script>
/* ================= i18n dictionary ================= */
const I18N = {
  zh: {
    title:'指定日期区间：查询状态为 B 的用户',
    dateFormat:'日期格式：YYYY-MM-DD',
    start:'开始日期',
    end:'结束日期',
    search:'查询',
    searching:'查询中...',
    range:'区间',
    count:'数量',
    noData:'没有数据',
    err_DATE_INVALID:'日期格式必须是 YYYY-MM-DD',
    err_DATE_RANGE:'开始日期不能大于结束日期',
    err_REQUEST_FAIL:'请求失败'
  },
  ja: {
    title:'指定した日付区間：ステータスがBのユーザー検索',
    dateFormat:'日付形式：YYYY-MM-DD',
    start:'開始日',
    end:'終了日',
    search:'検索',
    searching:'検索中...',
    range:'期間',
    count:'件数',
    noData:'該当データはありません',
    err_DATE_INVALID:'日付形式は YYYY-MM-DD で入力してください',
    err_DATE_RANGE:'開始日は終了日より前の日付を指定してください',
    err_REQUEST_FAIL:'通信エラーが発生しました'
  },
  en: {
    title:'Date Range: Users with Status B',
    dateFormat:'Date format: YYYY-MM-DD',
    start:'Start date',
    end:'End date',
    search:'Search',
    searching:'Searching...',
    range:'Range',
    count:'Count',
    noData:'No data',
    err_DATE_INVALID:'Date format must be YYYY-MM-DD',
    err_DATE_RANGE:'Start date must not be later than end date',
    err_REQUEST_FAIL:'Request failed'
  }
};

/* ================= language control ================= */
function detectLang(){
  const p=new URLSearchParams(location.search).get('lang');
  if(p && I18N[p]) return p;
  const s=localStorage.getItem('lang');
  if(s && I18N[s]) return s;
  const nav=(navigator.language||'en').toLowerCase();
  if(nav.startsWith('zh')) return 'zh';
  if(nav.startsWith('ja')) return 'ja';
  return 'en';
}

let lang = detectLang();
function t(k){ return I18N[lang][k] || k; }

function setLang(l){
  lang=l;
  localStorage.setItem('lang',l);
  render();
}

/* ================= render ================= */
function render(){
  $('#title').text(t('title'));
  $('#dateFormat').text(t('dateFormat'));
  $('#labelStart').text(t('start'));
  $('#labelEnd').text(t('end'));
  $('#btn').text(t('search'));
}
render();

/* ================= action ================= */
$('#btn').on('click',function(){
  const start=$('#start').val();
  const end=$('#end').val();
  $('#result').html('<div class="muted">'+t('searching')+'</div>');

  $.getJSON('/api.php',{start,end,lang})
    .done(function(res){
      if(!res.ok){
        $('#result').html('<div style="color:red;">'+t('err_'+res.code)+'</div>');
        return;
      }

      let html=`<div>${t('range')}: ${res.start} ~ ${res.end}　${t('count')}: ${res.count}</div>`;
      if(res.rows.length===0){
        html+=`<div class="muted">${t('noData')}</div>`;
      }else{
        html+='<table><thead><tr><th>ID</th><th>User</th></tr></thead><tbody>';
        res.rows.forEach(r=>{
          html+=`<tr><td>${r.id}</td><td>${r.user_name}</td></tr>`;
        });
        html+='</tbody></table>';
      }
      $('#result').html(html);
    })
    .fail(()=>$('#result').html('<div style="color:red;">'+t('err_REQUEST_FAIL')+'</div>'));
});
</script>
</body>
</html>
