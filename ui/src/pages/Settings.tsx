import { useCallback, useEffect, useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import ErrorState from '../components/common/ErrorState';
import LoadingState from '../components/common/LoadingState';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Switch } from '../components/ui/switch';
import type { Settings as SettingsModel } from '../features/settings/types';
import { getSettings, saveSettings } from '../features/settings/settingsService';

type LoadState =
  | { status: 'loading' }
  | { status: 'error'; message: string }
  | { status: 'ready' };

const defaultSettings: SettingsModel = {
  apiEndpoint: '',
  enableDebug: false,
  loadFrontendAssets: false,
};

function Settings() {
  const [loadState, setLoadState] = useState<LoadState>({
    status: 'loading',
  });
  const [formState, setFormState] = useState<SettingsModel>(defaultSettings);
  const [isSaving, setIsSaving] = useState(false);

  const fetchSettings = useCallback(async () => {
    setLoadState({ status: 'loading' });
    try {
      const data = await getSettings();
      setFormState(data);
      setLoadState({ status: 'ready' });
    } catch (error) {
      const message =
        error instanceof Error ? error.message : 'Failed to load settings.';
      setLoadState({ status: 'error', message });
    }
  }, []);

  useEffect(() => {
    void fetchSettings();
  }, [fetchSettings]);

  const handleSave = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setIsSaving(true);
    try {
      const saved = await saveSettings(formState);
      setFormState(saved);
      toast.success('Settings saved.');
    } catch (error) {
      const message =
        error instanceof Error ? error.message : 'Failed to save settings.';
      toast.error(message);
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <section className="space-y-6">
      <div>
        <h2 className="text-2xl font-semibold text-slate-900">Settings</h2>
        <p className="text-sm text-slate-600">
          Configure Forge Admin Suite defaults and debug controls.
        </p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>General</CardTitle>
        </CardHeader>
        <CardContent>
          {loadState.status === 'loading' && (
            <LoadingState message="Loading settings..." />
          )}
          {loadState.status === 'error' && (
            <ErrorState message={loadState.message} onRetry={fetchSettings} />
          )}
          {loadState.status === 'ready' && (
            <form className="space-y-6" onSubmit={handleSave}>
              <div className="space-y-2">
                <label
                  htmlFor="api-endpoint"
                  className="text-sm font-medium text-slate-900"
                >
                  API Endpoint
                </label>
                <Input
                  id="api-endpoint"
                  placeholder="https://api.example.com"
                  value={formState.apiEndpoint}
                  onChange={(event) =>
                    setFormState((prev) => ({
                      ...prev,
                      apiEndpoint: event.target.value,
                    }))
                  }
                />
                <p className="text-xs text-slate-500">
                  Leave blank to disable the external API override.
                </p>
              </div>

              <div className="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                <div>
                  <p className="text-sm font-medium text-slate-900">
                    Enable debug mode
                  </p>
                  <p className="text-xs text-slate-500">
                    Expose extra diagnostic output in the admin app.
                  </p>
                </div>
                <Switch
                  checked={formState.enableDebug}
                  onCheckedChange={(value: boolean) =>
                    setFormState((prev) => ({
                      ...prev,
                      enableDebug: value,
                    }))
                  }
                />
              </div>

              <div className="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                <div>
                  <p className="text-sm font-medium text-slate-900">
                    Load frontend assets
                  </p>
                  <p className="text-xs text-slate-500">
                    Enable loading Forge Admin Suite JS/CSS on the public site.
                  </p>
                </div>
                <Switch
                  checked={formState.loadFrontendAssets}
                  onCheckedChange={(value: boolean) =>
                    setFormState((prev) => ({
                      ...prev,
                      loadFrontendAssets: value,
                    }))
                  }
                />
              </div>

              <div className="flex justify-end">
                <Button type="submit" disabled={isSaving}>
                  {isSaving ? 'Saving...' : 'Save settings'}
                </Button>
              </div>
            </form>
          )}
        </CardContent>
      </Card>
    </section>
  );
}

export default Settings;
