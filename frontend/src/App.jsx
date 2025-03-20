import { useState, useEffect } from 'react';
import './index.css';

function App() {
    const [strategy, setStrategy] = useState('numeric');
    const [results, setResults] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const strategies = [
        { id: 'numeric', name: 'Numeric' },
        { id: 'three_char', name: 'Three Characters' },
        { id: 'three_char_assorted', name: 'Three Characters Assorted' },
        { id: 'dictionary', name: 'Dictionary' },
        { id: 'mixed', name: 'Mixed' },
        { id: 'all', name: 'All Strategies' },
        { id: 'answers', name: 'Answers' }
    ];

    const fetchResults = async (selectedStrategy) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch(`http://localhost:8080/?action=${selectedStrategy}`);

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            setResults(data);
        } catch (err) {
            console.error('Error fetching results:', err);
            setError('Failed to fetch results. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const handleStrategyChange = (e) => {
        const newStrategy = e.target.value;
        setStrategy(newStrategy);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        fetchResults(strategy);
    };

    return (
        <div className="min-h-screen bg-gray-100 py-8">
            <div className="max-w-4xl mx-auto px-4">
                <h1 className="text-3xl font-bold text-center mb-8 text-gray-800">Password Cracker</h1>

                <div className="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="strategy" className="block text-sm font-medium text-gray-700 mb-1">
                                Select Cracking Strategy
                            </label>
                            <select
                                id="strategy"
                                value={strategy}
                                onChange={handleStrategyChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                {strategies.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <button
                            type="submit"
                            className="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            disabled={loading}
                        >
                            {loading ? 'Processing...' : 'Crack Passwords'}
                        </button>
                    </form>
                </div>

                {error && (
                    <div className="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        {error}
                    </div>
                )}

                {loading && (
                    <div className="text-center py-4">
                        <div className="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                        <p className="mt-2 text-gray-600">Cracking passwords...</p>
                    </div>
                )}

                {results && (
                    <div className="bg-white rounded-lg shadow-md p-6">
                        <h2 className="text-xl font-semibold mb-4">Results</h2>
                        {results.error ? (
                            <p className="text-red-600">{results.error}</p>
                        ) : (
                            <div>
                                <div className="flex justify-between mb-4">
                                    <div className="bg-indigo-50 p-3 rounded">
                                        <span className="font-medium">Cracked Passwords:</span> {results.count || 0}
                                    </div>
                                    <div className="bg-indigo-50 p-3 rounded">
                                        <span className="font-medium">Duration:</span> {results.duration ? `${(results.duration * 1000).toFixed(2)} ms` : '0 ms'}
                                    </div>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hash</th>
                                        </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                        {results.cracked && results.cracked.length > 0 ? (
                                            results.cracked.map((item, index) => (
                                                <tr key={index} className={index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                                                    <td className="px-6 py-4 whitespace-nowrap">{item.user_id || '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-green-600">{item.password || '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-gray-500 font-mono text-sm">{item.hash || '-'}</td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="3" className="px-6 py-4 text-center">No passwords cracked</td>
                                            </tr>
                                        )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

export default App;