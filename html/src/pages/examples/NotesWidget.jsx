import React, { useEffect, useMemo, useState } from 'react';
import { api } from '@/lib/api';
import { Button, Card, Input } from '@/components/ui';

/**
 * ActiveRecord example, wired into the chain: a Note attaches to a Task
 * (note.task_uuid === task.uuid), and Tasks belong to the current Project.
 * The list is filtered to notes whose task belongs to this project.
 */
export default function NotesWidget({ tasks = [] }) {
  const [notes, setNotes] = useState([]);
  const [body, setBody] = useState('');
  const [taskUuid, setTaskUuid] = useState('');

  // uuid -> task title, for this project's tasks
  const taskByUuid = useMemo(() => {
    const map = {};
    tasks.forEach((t) => {
      if (t.uuid) map[t.uuid] = t.title;
    });
    return map;
  }, [tasks]);

  // Default the picker to the first task once tasks load.
  useEffect(() => {
    if (!taskUuid && tasks.length > 0) setTaskUuid(tasks[0].uuid);
  }, [tasks, taskUuid]);

  const load = async () => {
    const res = await api.get('/note');
    const data = await res.json();
    if (res.ok && Array.isArray(data)) {
      // Only notes that belong to a task of this project.
      setNotes(data.filter((n) => n.taskUuid && taskByUuid[n.taskUuid]));
    }
  };

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [taskByUuid]);

  const add = async (e) => {
    e.preventDefault();
    const res = await api.post('/note', { taskUuid, body });
    if (res.ok) {
      setBody('');
      load();
    }
  };

  if (tasks.length === 0) {
    return (
      <div>
        <h2 className="mb-2 text-lg font-semibold text-slate-700">Notes (ActiveRecord)</h2>
        <p className="text-slate-500">Add a task first — notes attach to a task.</p>
      </div>
    );
  }

  return (
    <div>
      <h2 className="mb-2 text-lg font-semibold text-slate-700">Notes (ActiveRecord)</h2>
      <Card className="mb-4 max-w-lg p-4">
        <form onSubmit={add} className="space-y-2">
          <select
            value={taskUuid}
            onChange={(e) => setTaskUuid(e.target.value)}
            className="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-600 focus:border-brand focus:outline-none"
          >
            {tasks.map((t) => (
              <option key={t.uuid} value={t.uuid}>
                {t.title}
              </option>
            ))}
          </select>
          <div className="flex items-center gap-2">
            <Input value={body} onChange={(e) => setBody(e.target.value)} placeholder="Write a note…" required />
            <Button type="submit">Add</Button>
          </div>
        </form>
      </Card>
      <ul className="space-y-1 text-sm text-slate-600">
        {notes.map((n) => (
          <li key={n.id} className="rounded bg-white px-3 py-2 shadow-sm">
            <span>{n.body}</span>
            {taskByUuid[n.taskUuid] && (
              <span className="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-400">
                {taskByUuid[n.taskUuid]}
              </span>
            )}
          </li>
        ))}
        {notes.length === 0 && <p className="text-slate-500">No notes for this project yet.</p>}
      </ul>
    </div>
  );
}
