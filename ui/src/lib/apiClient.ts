import { getAppConfig } from './appConfig';

type ApiError = Error & {
  status?: number;
  code?: string;
};

async function safeJson(response: Response) {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

function createError(message: string, status?: number, code?: string): ApiError {
  const error = new Error(message) as ApiError;
  if (status !== undefined) {
    error.status = status;
  }
  if (code) {
    error.code = code;
  }
  return error;
}

async function request<T>(path: string, init: RequestInit): Promise<T> {
  const { restUrl, nonce } = getAppConfig();
  const normalized = path.replace(/^\/+/, '');
  const url = `${restUrl}${normalized}`;

  const response = await fetch(url, {
    ...init,
    headers: {
      'X-WP-Nonce': nonce,
      ...(init.headers ?? {}),
    },
  });

  const payload = await safeJson(response);

  if (!response.ok) {
    const message =
      (payload && typeof payload.message === 'string' && payload.message) ||
      `Request failed with ${response.status}`;
    const code = payload && typeof payload.code === 'string' ? payload.code : undefined;
    throw createError(message, response.status, code);
  }

  return payload as T;
}

export function apiGet<T>(path: string): Promise<T> {
  return request<T>(path, { method: 'GET' });
}

export function apiPost<TReq, TRes>(path: string, body: TReq): Promise<TRes> {
  return request<TRes>(path, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
  });
}
