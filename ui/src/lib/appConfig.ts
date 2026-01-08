type AppConfig = {
  restUrl: string;
  nonce: string;
  pluginVersion: string;
  env: {
    mode: 'dev' | 'prod';
    viteAvailable: boolean;
    viteOrigin: string;
    entry: string;
    manifestPath: string;
  };
};

export function getAppConfig(): AppConfig {
  const config = window.__FORGE_ADMIN_SUITE__;

  if (!config) {
    throw new Error(
      'Forge Admin Suite config is missing. Ensure the plugin enqueues app data.'
    );
  }

  return config;
}
