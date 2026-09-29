import React from 'react';
import { createRoot } from 'react-dom/client';
import {
  Activity,
  Bot,
  CheckCircle2,
  Copy,
  CreditCard,
  Gauge,
  KeyRound,
  LayoutDashboard,
  MessageCircle,
  Package,
  PlugZap,
  RefreshCw,
  Search,
  Settings,
  ShieldCheck,
  Smartphone,
  Users,
} from 'lucide-react';
import '../css/app.css';

const stats = [
  ['Active clients', '128', '+14', Users],
  ['AI replies', '48.2k', '+31%', Bot],
  ['Token spend', '$184', '-12%', Gauge],
  ['Open handoffs', '19', 'live', MessageCircle],
];

const navItems = [
  ['Overview', LayoutDashboard],
  ['Clients', Users],
  ['Packages', Package],
  ['API Keys', KeyRound],
  ['Channels', PlugZap],
  ['Usage', Activity],
  ['Billing', CreditCard],
  ['Settings', Settings],
];

const clients = [
  ['BoneekBD', 'Business', 'Messenger, Website', 'Active', '82%'],
  ['Style Hut', 'Starter', 'Website', 'Trial', '24%'],
  ['Gadget Zone', 'Premium', 'WhatsApp, Messenger', 'Active', '61%'],
  ['Home Craft', 'Growth', 'Website, WhatsApp', 'Past due', '93%'],
];

const channels = [
  ['Messenger', '34 pages', '31 live', MessageCircle],
  ['WhatsApp', '18 numbers', '15 live', Smartphone],
  ['Website chat', '96 sites', '91 live', Bot],
];

const tasks = [
  ['Cloud package mode', 'Next'],
  ['Client API key guard', 'Next'],
  ['Messenger OAuth connect', 'Queued'],
  ['WhatsApp health jobs', 'Queued'],
];

function App() {
  return (
    <main className="min-h-screen bg-slate-100 text-slate-950">
      <aside className="fixed inset-y-0 left-0 hidden w-64 border-r border-slate-200 bg-white lg:block">
        <div className="flex h-16 items-center gap-3 border-b border-slate-200 px-5">
          <div className="grid h-9 w-9 place-items-center rounded-md bg-emerald-600 text-sm font-bold text-white">DN</div>
          <div>
            <h1 className="text-sm font-semibold">Devniox AI</h1>
            <div className="text-xs font-medium text-slate-500">Automation SaaS</div>
          </div>
        </div>
        <nav className="space-y-1 p-3">
          {navItems.map(([label, Icon], index) => (
            <button key={label} className={`nav-item ${index === 0 ? 'active' : ''}`}>
              <Icon size={17} />
              <span>{label}</span>
            </button>
          ))}
        </nav>
      </aside>

      <section className="lg:pl-64">
        <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:px-7">
          <div className="flex items-center gap-3">
            <h2 className="text-lg font-semibold">Command Center</h2>
            <span className="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Live</span>
          </div>
          <div className="flex items-center gap-2">
            <button className="icon-button" title="Search"><Search size={18} /></button>
            <button className="icon-button" title="Refresh"><RefreshCw size={18} /></button>
            <button className="primary-button"><PlugZap size={17} /> Connect channel</button>
          </div>
        </header>

        <div className="space-y-5 p-4 lg:p-7">
          <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {stats.map(([label, value, delta, Icon]) => (
              <article key={label} className="metric-card">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium text-slate-500">{label}</span>
                  <Icon size={18} className="text-slate-500" />
                </div>
                <div className="mt-4 flex items-end justify-between">
                  <strong className="text-3xl font-semibold tracking-normal">{value}</strong>
                  <span className="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{delta}</span>
                </div>
              </article>
            ))}
          </section>

          <section className="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div className="panel">
              <div className="panel-header">
                <h3>Clients</h3>
                <button className="secondary-button"><Copy size={16} /> New API key</button>
              </div>
              <div className="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Business</th>
                      <th>Package</th>
                      <th>Channels</th>
                      <th>Status</th>
                      <th>Usage</th>
                    </tr>
                  </thead>
                  <tbody>
                    {clients.map(([name, plan, channel, status, usage]) => (
                      <tr key={name}>
                        <td className="font-semibold">{name}</td>
                        <td>{plan}</td>
                        <td>{channel}</td>
                        <td><span className={`status ${status.toLowerCase().replace(' ', '-')}`}>{status}</span></td>
                        <td>
                          <div className="usage"><span style={{ width: usage }} /></div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            <div className="panel">
              <div className="panel-header">
                <h3>Channels</h3>
                <ShieldCheck size={18} className="text-emerald-600" />
              </div>
              <div className="space-y-3">
                {channels.map(([name, total, live, Icon]) => (
                  <article key={name} className="channel-row">
                    <div className="flex items-center gap-3">
                      <div className="channel-icon"><Icon size={18} /></div>
                      <div>
                        <div className="font-semibold">{name}</div>
                        <div className="text-xs font-medium text-slate-500">{total}</div>
                      </div>
                    </div>
                    <span className="rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">{live}</span>
                  </article>
                ))}
              </div>
            </div>
          </section>

          <section className="grid gap-5 xl:grid-cols-[1fr_1fr]">
            <div className="panel">
              <div className="panel-header">
                <h3>Build Queue</h3>
                <Package size={18} className="text-slate-500" />
              </div>
              <div className="task-grid">
                {tasks.map(([name, state]) => (
                  <div key={name} className="task-item">
                    <CheckCircle2 size={17} />
                    <span>{name}</span>
                    <strong>{state}</strong>
                  </div>
                ))}
              </div>
            </div>

            <div className="panel">
              <div className="panel-header">
                <h3>API Key Preview</h3>
                <KeyRound size={18} className="text-slate-500" />
              </div>
              <div className="api-preview">
                <span>dnx_live_boneekbd_••••_8fa2</span>
                <button className="icon-button" title="Copy"><Copy size={17} /></button>
              </div>
            </div>
          </section>
        </div>
      </section>
    </main>
  );
}

createRoot(document.getElementById('root')).render(<App />);
