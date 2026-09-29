import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  Activity,
  Bell,
  Bot,
  Camera,
  Check,
  Copy,
  CreditCard,
  FileText,
  Gauge,
  Home,
  KeyRound,
  LayoutDashboard,
  MessageCircle,
  Package,
  PlugZap,
  Radio,
  RefreshCw,
  Search,
  Settings,
  ShieldCheck,
  Smartphone,
  Users,
} from 'lucide-react';
import '../css/app.css';

const fallbackData = {
  role: 'super_admin',
  business: null,
  metrics: [],
  plans: [],
  clients: [],
  channels: [],
  providers: [],
  automation: [],
  reply_flow: [],
};

const navItems = [
  ['Home', Home],
  ['Dashboard', LayoutDashboard],
  ['Clients', Users],
  ['Plans', Package],
  ['Channels', PlugZap],
  ['AI Providers', Bot],
  ['Billing', CreditCard],
  ['Docs', FileText],
  ['Settings', Settings],
];

const channelIcons = {
  Messenger: MessageCircle,
  Instagram: Camera,
  WhatsApp: Smartphone,
  'Website Chat': Bot,
};

const metricIcons = [Users, Bot, Radio, Gauge];

function App() {
  const [active, setActive] = useState('Dashboard');
  const [user, setUser] = useState(null);
  const [data, setData] = useState(fallbackData);
  const [docs, setDocs] = useState({ sections: [] });
  const [loading, setLoading] = useState(true);
  const [authLoading, setAuthLoading] = useState(true);

  useEffect(() => {
    portalRequest('/portal-api/me')
      .then((payload) => setUser(payload.user))
      .finally(() => setAuthLoading(false));
  }, []);

  useEffect(() => {
    if (!user) {
      return;
    }

    setLoading(true);
    Promise.all([
      portalRequest('/portal-api/overview'),
      portalRequest('/api/v1/documentation'),
    ])
      .then(([overview, documentation]) => {
        setData(overview);
        setDocs(documentation);
      })
      .finally(() => setLoading(false));
  }, [user]);

  const activeClients = useMemo(
    () => (data.clients || []).filter((client) => client.status === 'Active').length,
    [data.clients],
  );

  const isCustomer = user?.role === 'customer';

  if (authLoading) {
    return <SplashScreen />;
  }

  if (!user) {
    return <LoginScreen onLogin={setUser} />;
  }

  return (
    <main className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand-mark">DN</div>
          <div>
            <h1>Devniox AI</h1>
            <span>SaaS Console</span>
          </div>
        </div>

        <nav className="nav-list">
          {navItems.map(([label, Icon]) => (
            <button key={label} className={`nav-item ${active === label ? 'active' : ''}`} onClick={() => setActive(label)}>
              <Icon size={17} />
              <span>{label}</span>
            </button>
          ))}
        </nav>
      </aside>

      <section className="workspace">
        <header className="topbar">
          <div className="topbar-title">
            <strong>{isCustomer && data.business ? data.business.name : active}</strong>
            <span>{loading ? 'Syncing' : 'Live'}</span>
          </div>
          <div className="topbar-actions">
            <span className="role-badge">{isCustomer ? 'Customer' : 'Super Admin'}</span>
            <button className="icon-button" title="Search"><Search size={18} /></button>
            <button className="icon-button" title="Notifications"><Bell size={18} /></button>
            <button className="icon-button" title="Refresh"><RefreshCw size={18} /></button>
            <button className="primary-button"><PlugZap size={17} /> Connect</button>
            <button className="secondary-button" onClick={() => logout(setUser)}>Logout</button>
          </div>
        </header>

        <div className="mobile-tabs">
          {['Home', 'Dashboard', 'Plans', 'Docs'].map((label) => (
            <button key={label} className={active === label ? 'selected' : ''} onClick={() => setActive(label)}>{label}</button>
          ))}
        </div>

        <div className="content">
          {active === 'Home' && <HomeScreen data={data} activeClients={activeClients} />}
          {active === 'Dashboard' && <DashboardScreen data={data} isCustomer={isCustomer} />}
          {active === 'Clients' && (isCustomer ? <CustomerSetupScreen data={data} /> : <ClientsScreen clients={data.clients || []} />)}
          {active === 'Plans' && <PlansScreen plans={data.plans || []} />}
          {active === 'Channels' && <ChannelsScreen channels={data.channels || []} />}
          {active === 'AI Providers' && <ProvidersScreen providers={data.providers || []} />}
          {active === 'Billing' && <BillingScreen plans={data.plans || []} />}
          {active === 'Docs' && <DocsScreen docs={docs} />}
          {active === 'Settings' && <SettingsScreen />}
        </div>
      </section>
    </main>
  );
}

function LoginScreen({ onLogin }) {
  const [email, setEmail] = useState('admin@admin.com');
  const [password, setPassword] = useState('12345678');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  function submit(event) {
    event.preventDefault();
    setSubmitting(true);
    setError('');

    portalRequest('/portal-api/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })
      .then((payload) => onLogin(payload.user))
      .catch(() => setError('Login failed'))
      .finally(() => setSubmitting(false));
  }

  return (
    <main className="login-shell">
      <section className="login-hero">
        <span className="eyebrow">Devniox AI SaaS</span>
        <h1>Automation Portal</h1>
        <div className="login-quick">
          <button type="button" onClick={() => setEmail('admin@admin.com')}>Super Admin</button>
          <button type="button" onClick={() => setEmail('rumank@gmail.com')}>Customer</button>
        </div>
      </section>
      <form className="login-card" onSubmit={submit}>
        <h2>Login</h2>
        <label>
          <span>Email</span>
          <input type="email" value={email} onChange={(event) => setEmail(event.target.value)} />
        </label>
        <label>
          <span>Password</span>
          <input type="password" value={password} onChange={(event) => setPassword(event.target.value)} />
        </label>
        {error && <div className="form-error">{error}</div>}
        <button className="primary-button" type="submit" disabled={submitting}>
          <ShieldCheck size={17} /> {submitting ? 'Checking' : 'Login'}
        </button>
      </form>
    </main>
  );
}

function SplashScreen() {
  return (
    <main className="splash-screen">
      <div className="brand-mark">DN</div>
      <strong>Loading</strong>
    </main>
  );
}

function HomeScreen({ data, activeClients }) {
  return (
    <div className="screen-grid">
      <section className="hero-band">
        <div>
          <span className="eyebrow">AI Support SaaS</span>
          <h2>Devniox Automation</h2>
          <div className="hero-actions">
            <button className="primary-button"><Package size={17} /> Launch plan</button>
            <button className="secondary-button"><FileText size={17} /> Docs</button>
          </div>
        </div>
        <div className="hero-stats">
          <Metric label="Active clients" value={activeClients} />
          <Metric label="Provider modes" value={data.providers.length} />
          <Metric label="Channel types" value={data.channels.length} />
        </div>
      </section>

      <section className="module-grid">
        {[
          ['Messenger', 'Page inbox', MessageCircle],
          ['Instagram', 'DM automation', Camera],
          ['WhatsApp', 'Cloud API', Smartphone],
          ['Website', 'Laravel package', Bot],
        ].map(([title, label, Icon]) => (
          <article key={title} className="module-card">
            <Icon size={20} />
            <strong>{title}</strong>
            <span>{label}</span>
          </article>
        ))}
      </section>
    </div>
  );
}

function DashboardScreen({ data, isCustomer }) {
  return (
    <div className="screen-grid">
      <section className="metric-grid">
        {data.metrics.map((metric, index) => {
          const Icon = metricIcons[index] || Activity;

          return (
            <article key={metric.label} className="metric-card">
              <div className="metric-head">
                <span>{metric.label}</span>
                <Icon size={18} />
              </div>
              <div className="metric-value">
                <strong>{metric.value}</strong>
                <em>{metric.trend}</em>
              </div>
            </article>
          );
        })}
      </section>

      <section className="split-grid">
        {isCustomer ? <CustomerSetupScreen data={data} compact /> : <ClientsScreen clients={data.clients || []} compact />}
        <ChannelsScreen channels={data.channels || []} compact />
      </section>

      <section className="split-grid">
        <ProvidersScreen providers={data.providers || []} compact />
        <AutomationScreen items={data.automation || []} />
      </section>

      {isCustomer && <ReplyFlowScreen items={data.reply_flow || []} />}
    </div>
  );
}

function CustomerSetupScreen({ data, compact = false }) {
  const business = data.business || {};

  return (
    <section className="panel">
      <PanelHeader title="Customer Setup" action="API key" icon={KeyRound} />
      <div className="billing-grid">
        <Metric label="Business" value={business.name || 'New'} />
        <Metric label="Plan" value={business.plan || 'Starter'} />
        <Metric label="Status" value={business.status || 'Draft'} />
        <Metric label="API Prefix" value={business.api_key_prefix || 'dnx_live'} />
      </div>
      {!compact && (
        <div className="setup-grid">
          <ConnectionBox title="OpenAI" value="Own API key" />
          <ConnectionBox title="Claude" value="Own API key" />
          <ConnectionBox title="Messenger" value="Facebook page" />
          <ConnectionBox title="Website" value="Live chat package" />
        </div>
      )}
    </section>
  );
}

function ReplyFlowScreen({ items }) {
  return (
    <section className="panel">
      <PanelHeader title="Auto Reply Flow" action="Test" icon={Bot} />
      <div className="flow-grid">
        {items.map((item) => (
          <article key={item.step} className="doc-line">
            <Check size={15} />
            <strong>{item.step}</strong>
            <span>{item.channel}</span>
          </article>
        ))}
      </div>
    </section>
  );
}

function ConnectionBox({ title, value }) {
  return (
    <article className="connection-box">
      <strong>{title}</strong>
      <span>{value}</span>
      <button className="secondary-button"><PlugZap size={16} /> Connect</button>
    </article>
  );
}

function ClientsScreen({ clients, compact = false }) {
  return (
    <section className="panel">
      <PanelHeader title="Clients" action="New API key" icon={KeyRound} />
      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Business</th>
              <th>Plan</th>
              <th>Channels</th>
              <th>AI</th>
              <th>Status</th>
              <th>Usage</th>
            </tr>
          </thead>
          <tbody>
            {clients.map((client) => (
              <tr key={client.name}>
                <td className="strong-cell">{client.name}</td>
                <td>{client.plan}</td>
                <td>{client.channels.join(', ')}</td>
                <td>{client.provider}</td>
                <td><span className={`status ${client.status.toLowerCase().replace(' ', '-')}`}>{client.status}</span></td>
                <td><Progress value={client.usage} /></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {!compact && <ApiKeyBox />}
    </section>
  );
}

function PlansScreen({ plans }) {
  return (
    <section className="panel">
      <PanelHeader title="Packages" action="Add plan" icon={Package} />
      <div className="plan-grid">
        {plans.map((plan) => (
          <article key={plan.name} className="plan-card">
            <div>
              <span className="eyebrow">{plan.cycle}</span>
              <h3>{plan.name}</h3>
            </div>
            <strong>BDT {plan.price}</strong>
            <div className="plan-limits">
              <span>{plan.messages.toLocaleString()} messages</span>
              <span>{plan.channels} channels</span>
            </div>
            <button className="secondary-button"><Check size={16} /> Select</button>
          </article>
        ))}
      </div>
    </section>
  );
}

function ChannelsScreen({ channels, compact = false }) {
  return (
    <section className="panel">
      <PanelHeader title="Channels" action="Connect" icon={PlugZap} />
      <div className="channel-grid">
        {channels.map((channel) => {
          const Icon = channelIcons[channel.name] || MessageCircle;

          return (
            <article key={channel.name} className="channel-row">
              <div className="channel-title">
                <div className="channel-icon"><Icon size={18} /></div>
                <div>
                  <strong>{channel.name}</strong>
                  <span>{channel.connected} connected</span>
                </div>
              </div>
              <span className="live-pill">{channel.status || `${channel.live} live`}</span>
            </article>
          );
        })}
      </div>
      {!compact && <WebhookBox />}
    </section>
  );
}

function ProvidersScreen({ providers, compact = false }) {
  return (
    <section className="panel">
      <PanelHeader title="AI Providers" action="Add key" icon={Bot} />
      <div className="provider-grid">
        {providers.map((provider) => (
          <article key={provider.name} className="provider-row">
            <ShieldCheck size={18} />
            <strong>{provider.name}</strong>
            <span>{provider.mode}</span>
            <em>{provider.status}</em>
          </article>
        ))}
      </div>
      {!compact && <TokenPolicy />}
    </section>
  );
}

function BillingScreen({ plans }) {
  return (
    <section className="panel">
      <PanelHeader title="Billing" action="Invoice" icon={CreditCard} />
      <div className="billing-grid">
        <Metric label="MRR" value="BDT 4.6L" />
        <Metric label="Trials" value="22" />
        <Metric label="Past due" value="4" />
        <Metric label="Yearly plans" value={plans.filter((plan) => plan.cycle === 'yearly').length} />
      </div>
    </section>
  );
}

function DocsScreen({ docs }) {
  return (
    <section className="panel">
      <PanelHeader title="Documentation" action="Export" icon={FileText} />
      <div className="docs-grid">
        {docs.sections.map((section) => (
          <article key={section.title} className="doc-card">
            <h3>{section.title}</h3>
            {section.items.map((item) => (
              <div key={item} className="doc-line">
                <Check size={15} />
                <span>{item}</span>
              </div>
            ))}
          </article>
        ))}
      </div>
    </section>
  );
}

function SettingsScreen() {
  return (
    <section className="panel">
      <PanelHeader title="Settings" action="Save" icon={Settings} />
      <div className="settings-grid">
        {['Tenant domains', 'Webhook secrets', 'Token guard', 'Realtime events'].map((item) => (
          <label key={item} className="setting-row">
            <span>{item}</span>
            <input type="checkbox" defaultChecked />
          </label>
        ))}
      </div>
    </section>
  );
}

function AutomationScreen({ items }) {
  return (
    <section className="panel">
      <PanelHeader title="Build Queue" action="Run" icon={Activity} />
      <div className="task-grid">
        {items.map((item) => (
          <div key={item.name} className="task-item">
            <Check size={17} />
            <span>{item.name}</span>
            <strong>{item.state}</strong>
          </div>
        ))}
      </div>
    </section>
  );
}

function PanelHeader({ title, action, icon: Icon }) {
  return (
    <div className="panel-header">
      <h3>{title}</h3>
      <button className="secondary-button"><Icon size={16} /> {action}</button>
    </div>
  );
}

function Metric({ label, value }) {
  return (
    <div className="small-metric">
      <span>{label}</span>
      <strong>{value}</strong>
    </div>
  );
}

function Progress({ value }) {
  return <div className="usage"><span style={{ width: `${value}%` }} /></div>;
}

function ApiKeyBox() {
  return (
    <div className="api-preview">
      <span>dnx_live_boneekbd_****_8fa2</span>
      <button className="icon-button" title="Copy"><Copy size={17} /></button>
    </div>
  );
}

function WebhookBox() {
  return (
    <div className="webhook-grid">
      <Metric label="Webhook URL" value="/api/devniox/webhook" />
      <Metric label="Realtime" value="Pusher ready" />
    </div>
  );
}

function TokenPolicy() {
  return (
    <div className="webhook-grid">
      <Metric label="Default mode" value="Managed" />
      <Metric label="Client mode" value="BYOK" />
    </div>
  );
}

function portalRequest(url, options = {}) {
  return fetch(url, {
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      ...(options.headers || {}),
    },
    ...options,
  }).then((response) => {
    if (!response.ok) {
      throw new Error(`Request failed: ${response.status}`);
    }

    return response.json();
  });
}

function logout(setUser) {
  portalRequest('/portal-api/logout', { method: 'POST' }).finally(() => {
    setUser(null);
  });
}

createRoot(document.getElementById('root')).render(<App />);
