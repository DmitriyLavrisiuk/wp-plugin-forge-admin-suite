import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import ErrorState from '../components/common/ErrorState';
import LoadingState from '../components/common/LoadingState';
import { apiGet, apiPost } from '../lib/apiClient';
import { getAppConfig } from '../lib/appConfig';
import { Button } from '../components/ui/button';

type PingResponse = {
  ok: boolean;
  version: string;
};

type RequestState =
  | { status: 'idle' | 'loading' }
  | { status: 'success'; data: PingResponse }
  | { status: 'error'; message: string };

function Dashboard() {
  const [state, setState] = useState<RequestState>({ status: 'loading' });
  const { pluginVersion, env } = getAppConfig();
  const [lastAutoRecheck, setLastAutoRecheck] = useState<string>('—');

  const fetchPing = useCallback(async () => {
    setState({ status: 'loading' });
    try {
      const data = await apiGet<PingResponse>('forge-admin-suite/v1/ping');
      setState({ status: 'success', data });
    } catch (error) {
      const message =
        error instanceof Error ? error.message : 'Something went wrong.';
      setState({ status: 'error', message });
      toast.error('Failed to load API status.');
    }
  }, []);

  useEffect(() => {
    void fetchPing();
  }, [fetchPing]);

  useEffect(() => {
    try {
      const raw = sessionStorage.getItem('forge_vite_recheck_ts');
      if (!raw) {
        return;
      }
      const parsed = Number(raw);
      if (Number.isNaN(parsed)) {
        return;
      }
      setLastAutoRecheck(new Date(parsed).toLocaleString());
    } catch {
      // ignore storage errors
    }
  }, []);

  const handleRecheck = useCallback(async () => {
    try {
      await apiPost<undefined, { ok: boolean }>(
        'forge-admin-suite/v1/dev/recheck-vite',
        undefined
      );
      toast.success('Recheck requested.');
      window.location.reload();
    } catch (error) {
      const message =
        error instanceof Error ? error.message : 'Failed to recheck Vite.';
      toast.error(message);
    }
  }, []);

  useEffect(() => {
    if (env.mode !== 'prod' || env.viteAvailable) {
      return;
    }

    try {
      const key = 'forge_vite_recheck_ts';
      const now = Date.now();
      const last = Number(sessionStorage.getItem(key) || 0);
      if (!last || now - last > 60_000) {
        sessionStorage.setItem(key, String(now));
        setLastAutoRecheck(new Date(now).toLocaleString());
        apiPost<undefined, { ok: boolean }>(
          'forge-admin-suite/v1/dev/recheck-vite',
          undefined
        )
          .then(() => {
            window.location.reload();
          })
          .catch(() => {
            // keep silent to avoid toast spam on auto retries
          });
      }
    } catch {
      // ignore storage errors
    }
  }, [env.mode, env.viteAvailable]);

  return (
    <section className="space-y-6">
      <div>
        <h2 className="text-2xl font-semibold text-slate-900">Dashboard</h2>
        <p className="text-sm text-slate-600">
          Live status from the Forge Admin Suite API.
        </p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>API Ping</CardTitle>
        </CardHeader>
        <CardContent>
          {state.status === 'loading' && (
            <LoadingState message="Loading API status..." />
          )}
          {state.status === 'error' && (
            <ErrorState message={state.message} onRetry={fetchPing} />
          )}
          {state.status === 'success' && (
            <div className="space-y-2 text-sm text-slate-700">
              <p>
                <span className="font-semibold">OK:</span>{' '}
                {state.data.ok ? 'true' : 'false'}
              </p>
              <p>
                <span className="font-semibold">Version:</span>{' '}
                {state.data.version}
              </p>
              <p>
                <span className="font-semibold">Plugin version:</span>{' '}
                {pluginVersion}
              </p>
              <div className="pt-2 text-xs text-slate-500">
                <p>
                  <span className="font-semibold">Mode:</span> {env.mode}
                </p>
                <p>
                  <span className="font-semibold">Vite available:</span>{' '}
                  {env.viteAvailable ? 'yes' : 'no'}
                </p>
                <p>
                  <span className="font-semibold">Vite origin:</span>{' '}
                  {env.viteOrigin || 'n/a'}
                </p>
                <p>
                  <span className="font-semibold">Entry:</span> {env.entry}
                </p>
                <p>
                  <span className="font-semibold">Manifest path:</span>{' '}
                  {env.manifestPath || 'n/a'}
                </p>
                <p className="text-xs text-slate-500">
                  <span className="font-semibold">Last auto recheck:</span>{' '}
                  {lastAutoRecheck}
                </p>
                <div className="pt-2">
                  <Button type="button" onClick={handleRecheck} className="h-7 px-3 text-xs">
                    Recheck Vite
                  </Button>
                </div>
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    </section>
  );
}

export default Dashboard;
