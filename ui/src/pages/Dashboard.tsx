import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import ErrorState from '../components/common/ErrorState';
import LoadingState from '../components/common/LoadingState';
import { apiGet } from '../lib/apiClient';
import { getAppConfig } from '../lib/appConfig';

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
  const { pluginVersion } = getAppConfig();

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
            </div>
          )}
        </CardContent>
      </Card>
    </section>
  );
}

export default Dashboard;
