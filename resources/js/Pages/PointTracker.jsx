import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';

export default function PointTracker({ date, entries, total, point }) {
  const [amount, setAmount] = useState('');
  const { errors } = usePage().props;

  const changeDate = (newDate) => {
    router.get('/point-tracker', { date: newDate }, { preserveState: true });
  };

  // Typing "30+40+60" and saving once creates all three entries in a
  // single request, instead of clicking + once per amount.
  const onAmountChange = (e) => {
    setAmount(e.target.value.replace(/[^0-9+.]/g, ''));
  };

  const addEntry = (e) => {
    e.preventDefault();
    if (!amount.trim()) return;
    router.post('/point-tracker', { entry_date: date, amount }, {
      preserveScroll: true,
      onSuccess: () => setAmount(''),
    });
  };

  const editEntry = (entry) => {
    const next = prompt('កែចំនួនទឹកប្រាក់៖', entry.amount);
    if (next === null) return;
    const val = parseFloat(next);
    if (isNaN(val) || val <= 0) return;
    router.put(`/point-tracker/${entry.id}`, { amount: val }, { preserveScroll: true });
  };

  const deleteEntry = (id) => {
    router.delete(`/point-tracker/${id}`, { preserveScroll: true });
  };

  const shiftDate = (days) => {
    const d = new Date(date);
    d.setDate(d.getDate() + days);
    changeDate(d.toISOString().slice(0, 10));
  };

  return (
    <div className="max-w-lg mx-auto p-4">
      <div className="flex items-center gap-2 mb-4">
        <button onClick={() => shiftDate(-1)} className="px-3 py-1 border rounded">‹</button>
        <input
          type="date"
          value={date}
          onChange={(e) => changeDate(e.target.value)}
          className="border rounded px-2 py-1"
        />
        <button onClick={() => shiftDate(1)} className="px-3 py-1 border rounded">›</button>
        <a href="/point-tracker/report" className="ml-auto text-sm text-amber-700 underline">
          សរុបប្រចាំខែ
        </a>
      </div>

      <form onSubmit={addEntry} className="flex gap-2 mb-4">
        <input
          type="text"
          inputMode="decimal"
          value={amount}
          onChange={onAmountChange}
          placeholder="លក់បានប៉ុន្មាន? (ឧ. 30+40+60)"
          className="flex-1 border rounded px-3 py-2"
        />
        <button type="submit" className="bg-amber-700 text-white px-4 rounded font-bold">រក្សាទុក</button>
      </form>
      {errors.amount && (
        <p className="text-red-500 text-xs -mt-3 mb-4">{errors.amount}</p>
      )}

      <div className="flex flex-wrap gap-2 items-center min-h-[40px] mb-4">
        {entries.length === 0 && (
          <p className="text-gray-400 text-sm w-full text-center py-4">មិនទាន់លក់អី ថ្ងៃនេះទេ</p>
        )}
        {entries.map((entry, i) => (
          <span key={entry.id} className="flex items-center gap-1">
            {i > 0 && <span className="text-gray-400">+</span>}
            <span
              onClick={() => editEntry(entry)}
              className="cursor-pointer border-b border-dashed border-green-600 text-green-700 px-1"
            >
              {entry.amount}
            </span>
            <button onClick={() => deleteEntry(entry.id)} className="text-red-500 text-xs font-bold">✕</button>
          </span>
          
        ))}
      </div>

      <div className="flex justify-between items-end border-t pt-3">
        <div>
          <div className="text-xs text-gray-500">សរុបលក់ថ្ងៃនេះ</div>
          <div className="text-xl font-bold">${total.toFixed(2)}</div>
        </div>
        <div className="text-right">
          <div className="text-xs text-gray-500">Point (÷5)</div>
          <div className="text-2xl font-extrabold text-amber-700">{point}</div>
        </div>
      </div>
    </div>
  );
}
