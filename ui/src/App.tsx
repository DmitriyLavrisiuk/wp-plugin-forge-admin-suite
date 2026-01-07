import { NavLink, Route, Routes } from 'react-router-dom';
import { Toaster } from 'sonner';
import Dashboard from './pages/Dashboard';
import Settings from './pages/Settings';
import ErrorBoundary from './components/common/ErrorBoundary';

const linkBase =
  'block rounded-lg px-3 py-2 text-sm font-medium transition-colors';

function App() {
  return (
    <ErrorBoundary>
      <div className="min-h-screen bg-slate-50">
        <header className="border-b border-slate-200 bg-white px-6 py-4">
          <h1 className="text-xl font-semibold text-slate-900">
            Forge Admin Suite
          </h1>
        </header>
        <div className="flex min-h-[calc(100vh-72px)] flex-col md:flex-row">
          <aside className="border-b border-slate-200 bg-white p-4 md:w-64 md:border-b-0 md:border-r">
            <nav className="space-y-1">
              <NavLink
                to="/"
                end
                className={({ isActive }) =>
                  `${linkBase} ${
                    isActive
                      ? 'bg-slate-900 text-white'
                      : 'text-slate-700 hover:bg-slate-100'
                  }`
                }
              >
                Dashboard
              </NavLink>
              <NavLink
                to="/settings"
                className={({ isActive }) =>
                  `${linkBase} ${
                    isActive
                      ? 'bg-slate-900 text-white'
                      : 'text-slate-700 hover:bg-slate-100'
                  }`
                }
              >
                Settings
              </NavLink>
            </nav>
          </aside>
          <main className="flex-1 p-6">
            <Routes>
              <Route path="/" element={<Dashboard />} />
              <Route path="/settings" element={<Settings />} />
            </Routes>
          </main>
        </div>
        <Toaster position="top-right" richColors />
      </div>
    </ErrorBoundary>
  );
}

export default App;
