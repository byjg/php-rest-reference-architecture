import React, { useEffect, useState } from 'react';
import { api } from '@/lib/api';
import { Button, Card, Input, Field } from '@/components/ui';

export default function Profile() {
  const [profile, setProfile] = useState(null);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const res = await api.get('/profile');
        const data = await res.json();
        if (!res.ok) throw new Error(data.error?.message || 'Failed to load profile');
        setProfile(data);
        setName(data.name || '');
        setEmail(data.email || '');
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  const onSave = async (e) => {
    e.preventDefault();
    setStatus('');
    setError('');
    try {
      const res = await api.put('/profile', { name, email });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error?.message || 'Update failed');
      setStatus('Saved.');
    } catch (err) {
      setError(err.message);
    }
  };

  if (loading) return <p className="text-slate-500">Loading…</p>;

  return (
    <Card className="max-w-md p-6">
      <h1 className="mb-4 text-xl font-bold text-slate-800">Your Profile</h1>
      {error && <p className="mb-4 text-sm text-red-600">{error}</p>}
      <form onSubmit={onSave} className="space-y-4">
        <Field label="User ID">
          <Input value={profile?.userid || ''} disabled />
        </Field>
        <Field label="Name">
          <Input value={name} onChange={(e) => setName(e.target.value)} />
        </Field>
        <Field label="Email">
          <Input type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
        </Field>
        <div className="flex items-center gap-3">
          <Button type="submit">Save changes</Button>
          {status && <span className="text-sm text-green-600">{status}</span>}
        </div>
      </form>
    </Card>
  );
}
