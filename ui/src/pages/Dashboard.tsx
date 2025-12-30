import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
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

  useEffect(() => {
    let isMounted = true;

    async function fetchPing() {
      try {
        const { restUrl, nonce } = getAppConfig();
        const response = await fetch(`${restUrl}forge-admin-suite/v1/ping`, {
          headers: {
            'X-WP-Nonce': nonce,
          },
        });

        if (!response.ok) {
          throw new Error(`Request failed with ${response.status}`);
        }

        const data = (await response.json()) as PingResponse;
        if (isMounted) {
          setState({ status: 'success', data });
        }
      } catch (error) {
        if (isMounted) {
          setState({
            status: 'error',
            message:
              error instanceof Error ? error.message : 'Something went wrong.',
          });
        }
      }
    }

    fetchPing();

    return () => {
      isMounted = false;
    };
  }, []);

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
            <p className="text-sm text-slate-600">Loading API status...</p>
          )}
          {state.status === 'error' && (
            <p className="text-sm text-red-600">{state.message}</p>
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
            </div>
          )}
        </CardContent>
      </Card>
    </section>
  );
}

export default Dashboard;
