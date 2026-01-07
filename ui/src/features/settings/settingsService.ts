import { apiGet, apiPost } from '../../lib/apiClient';
import type { Settings } from './types';

const SETTINGS_PATH = 'forge-admin-suite/v1/settings';

export function getSettings(): Promise<Settings> {
  return apiGet<Settings>(SETTINGS_PATH);
}

export function saveSettings(payload: Settings): Promise<Settings> {
  return apiPost<Settings, Settings>(SETTINGS_PATH, payload);
}
