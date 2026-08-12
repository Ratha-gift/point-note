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

      <div className="fixed bottom-0 inset-x-0 bg-white border-t p-3">
        <div className="max-w-lg mx-auto flex gap-2">
          <a
            href={`/point-tracker/report/export-pdf?month=${month}`}
            className="flex-1 text-center bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700"
          >
            Export PDF
          </a>
          <a
            href={`/point-tracker/report/export-excel?month=${month}`}
            className="flex-1 text-center bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-700"
          >
            Export Excel
          </a>
        </div>
      </div>
    </div>
  );
}
