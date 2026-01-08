/* eslint-disable no-unused-vars */
export {};

declare global {
  interface Window {
    __FORGE_ADMIN_SUITE__?: {
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
  }
}
