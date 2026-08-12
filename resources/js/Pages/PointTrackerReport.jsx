import { Link, router } from '@inertiajs/react';

export default function PointTrackerReport({ month, rows, monthTotal, monthPoint }) {
  const changeMonth = (e) => {
    router.get('/point-tracker/report', { month: e.target.value }, { preserveState: true });
  };

  return (
    <div className="max-w-lg mx-auto p-4 pb-24">
      <Link href="/point-tracker" className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-amber-700 mb-3">
        ‹ ត្រឡប់ក្រោយ
      </Link>

      <div className="flex justify-between items-center mb-4">
        <h1 className="text-xl font-bold">សរុប Point ប្រចាំខែ</h1>
        <input type="month" value={month} onChange={changeMonth} className="border rounded px-2 py-1" />
      </div>

      <div className="divide-y border rounded">
        {rows.length === 0 && (
          <p className="text-gray-400 text-sm text-center py-6">មិនទាន់មានទិន្នន័យខែនេះទេ</p>
        )}
        {rows.map((row) => (
          <div key={row.date} className="flex justify-between px-3 py-2 text-sm">
            <span className="text-gray-500">{row.date}</span>
            <span>${row.total.toFixed(2)}</span>
            <span className="font-bold text-amber-700 w-14 text-right">{row.point}</span>
          </div>
        ))}
      </div>

      <div className="flex justify-between font-extrabold text-lg mt-4 pt-3 border-t-2">
        <span>សរុប Point ខែនេះ</span>
        <span className="text-amber-700">{monthPoint}</span>
      </div>

      <div className="fixed bottom-0 inset-x-0 p-3 pointer-events-none">
        <div className="max-w-lg mx-auto flex gap-2 pointer-events-auto">
          <a
            href={`/point-tracker/report/export-pdf?month=${month}`}
            className="flex-1 flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-5 h-5 shrink-0">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <path d="M14 2v6h6" />
              <path d="M9 13h6" />
              <path d="M9 17h6" />
              <path d="M9 9h1" />
            </svg>
            Export PDF
          </a>
          <a
            href={`/point-tracker/report/export-excel?month=${month}`}
            className="flex-1 flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-5 h-5 shrink-0">
              <rect x="3" y="4" width="18" height="16" rx="2" />
              <path d="M3 10h18" />
              <path d="M3 16h18" />
              <path d="M9 4v16" />
              <path d="M15 4v16" />
            </svg>
            Export Excel
          </a>
        </div>
      </div>
    </div>
  );
}
