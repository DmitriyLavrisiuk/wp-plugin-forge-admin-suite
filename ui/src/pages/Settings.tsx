import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';

function Settings() {
  return (
    <section className="space-y-6">
      <div>
        <h2 className="text-2xl font-semibold text-slate-900">Settings</h2>
        <p className="text-sm text-slate-600">
          Configuration options will live here.
        </p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Coming soon</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-slate-700">
            Add settings controls for Forge Admin Suite.
          </p>
        </CardContent>
      </Card>
    </section>
  );
}

export default Settings;
