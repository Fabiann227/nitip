import re, sys, json, time, statistics, random
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timedelta
import requests

sys.stdout.reconfigure(encoding='utf-8')
BASE = 'https://nitip.76-13-22-7.sslip.io'
PW = 'password'
results = []  # (scenario, ok, status, seconds)

def email(i): return f'loadtest{i:02d}@student.uph.edu'

def new_session():
    s = requests.Session()
    s.headers['User-Agent'] = 'NitipStressTest/1.0'
    return s

TOKEN_RE = re.compile(r'name="_token"\s+value="([^"]+)"')

def token_from(sess, path):
    r = sess.get(BASE + path, timeout=30)
    m = TOKEN_RE.search(r.text)
    if not m:
        raise RuntimeError(f'no token on {path} ({r.status_code})')
    return m.group(1)

def login(i):
    s = new_session()
    t = token_from(s, '/login')
    r = s.post(BASE + '/login', data={'_token': t, 'email': email(i), 'password': PW}, timeout=30)
    if '/dashboard' not in r.url and '/admin' not in r.url:
        raise RuntimeError(f'login {i} failed -> {r.url} {r.status_code}')
    return s, token_from(s, '/dashboard')

def timed(scn, fn):
    t0 = time.perf_counter()
    ok, status = False, 0
    try:
        r = fn()
        status = r.status_code
        ok = status == 200
    except Exception:
        status = -1
    dt = time.perf_counter() - t0
    results.append((scn, ok, status, dt))
    return ok, status, dt

def stats(scn):
    xs = sorted(d for s, ok, st, d in results if s == scn)
    errs = sum(1 for s, ok, st, d in results if s == scn and not ok)
    if not xs: return None
    q = lambda p: xs[min(len(xs) - 1, int(p * len(xs)))]
    return dict(n=len(xs), err=errs, p50=round(q(.5), 3), p95=round(q(.95), 3),
                p99=round(q(.99), 3), max=round(xs[-1], 3), mean=round(sum(xs) / len(xs), 3))

# ---------------------------------------------------------------- S1 landing
def s1():
    print('S1 landing: 300 GET / @ 30 koneksi anonim')
    sessions = [new_session() for _ in range(30)]
    t0 = time.perf_counter()
    with ThreadPoolExecutor(30) as ex:
        futs = [ex.submit(timed, 'landing', lambda s=sessions[k % 30]: s.get(BASE + '/', timeout=60))
                for k in range(300)]
        for f in as_completed(futs): f.result()
    dur = time.perf_counter() - t0
    print(f'  selesai {dur:.1f}s -> {300/dur:.1f} req/s | {stats("landing")}')
    return dur

# ---------------------------------------------------------------- login pool
def build_pool(ids):
    pool = {}
    def one(i):
        for attempt in range(2):
            try:
                pool[i] = login(i); return
            except Exception as e:
                if attempt: print('  login gagal', i, e)
                time.sleep(1)
    with ThreadPoolExecutor(10) as ex:
        list(ex.map(one, ids))
    print(f'  login pool: {len(pool)}/{len(ids)} siap')
    return pool

# ---------------------------------------------------------------- S2 browse
def s2(pool):
    print('S2 browsing login: dashboard + /nitip + /orders x', len(pool), 'user')
    t0 = time.perf_counter(); n = 0
    with ThreadPoolExecutor(20) as ex:
        futs = []
        for i, (s, t) in pool.items():
            for path in ('/dashboard', '/nitip', '/orders'):
                futs.append(ex.submit(timed, 'browse', lambda s=s, p=path: s.get(BASE + p, timeout=60))); n += 1
        for f in as_completed(futs): f.result()
    dur = time.perf_counter() - t0
    print(f'  selesai {dur:.1f}s -> {n/dur:.1f} req/s | {stats("browse")}')

# ---------------------------------------------------------------- S3 posting
def payload(i, k, token):
    nb = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%dT%H:%M')
    return {
        '_token': token, 'service_category_id': '1',
        'title': f'Stress test titipan {i:02d}-{k}',
        'pickup_location': 'Food Court UPH (Gedung B Lt. 1)',
        'dropoff_location': 'Gedung D Lt. 5 (Ruang 501)',
        'needed_by': nb, 'service_fee': '3500',
        'items[0][name]': 'Nasi Ayam Stress', 'items[0][quantity]': '1',
        'items[0][estimated_price]': '20000',
    }

codes = []
def s3(pool):
    print('S3 posting titipan: 30 user x 2 POST /requests')
    lock_codes = []
    def post_one(i, k):
        s, t = pool[i]
        def fn():
            r = s.post(BASE + '/requests', data=payload(i, k, t), timeout=60, allow_redirects=False)
            if r.status_code == 302 and '/orders/NT-' in r.headers.get('Location', ''):
                lock_codes.append((i, r.headers['Location'].rsplit('/', 1)[-1]))
                r.status_code = 200  # count as ok
            return r
        timed('post_request', fn)
    t0 = time.perf_counter()
    with ThreadPoolExecutor(20) as ex:
        futs = [ex.submit(post_one, i, k) for i in list(pool)[:30] for k in (1, 2)]
        for f in as_completed(futs): f.result()
    dur = time.perf_counter() - t0
    codes.extend(lock_codes)
    print(f'  selesai {dur:.1f}s, {len(lock_codes)}/60 order dibuat | {stats("post_request")}')

# ---------------------------------------------------------------- S4 claim race
def s4(pool, rounds=5, racers=10):
    print(f'S4 claim race: {racers} relawan rebutan 1 titipan x {rounds} ronde')
    ids = list(pool)
    out = []
    for rnd in range(rounds):
        poster, code = codes[rnd]
        claimers = [i for i in ids if i != poster][:racers]
        def claim(i):
            s, t = pool[i]
            t0 = time.perf_counter()
            r = s.post(BASE + f'/orders/{code}/claim', data={'_token': t}, timeout=60)
            dt = time.perf_counter() - t0
            win = 'Kamu mengambil titipan ini' in r.text
            results.append(('claim', r.status_code == 200, r.status_code, dt))
            return win
        with ThreadPoolExecutor(racers) as ex:
            wins = sum(ex.map(claim, claimers))
        verdict = 'OK' if wins == 1 else '!! RACE BUG'
        print(f'  ronde {rnd+1}: order {code}: {wins} pemenang dari {racers} ({verdict})')
        out.append(dict(round=rnd + 1, code=code, winners=wins, racers=racers))
    return out

# ---------------------------------------------------------------- run
summary = {}
warm = requests.get(BASE + '/', timeout=60); print('warmup', warm.status_code)
d1 = s1()
pool = build_pool(range(1, 41))
s2(pool)
s3(pool)
race = s4(pool)
summary = dict(
    base=BASE, when=datetime.now().isoformat(timespec='seconds'),
    landing=stats('landing'), browse=stats('browse'),
    post_request=stats('post_request'), claim=stats('claim'),
    landing_rps=round(300 / d1, 1), race=race,
    total_requests=len(results), total_errors=sum(1 for _, ok, *_ in results if not ok),
)
print(json.dumps(summary, indent=1))
open('stress_results.json', 'w').write(json.dumps(summary, indent=1))
