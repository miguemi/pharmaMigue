import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import MainLayout from "../Layouts/MainLayout";

export default function Create({ user, tenant }) {
    const [form, setForm] = useState({
        name: "",
        description: "",
    });

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm({
            ...form,
            [name]: value,
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        router.post(`/tenant/${tenant.code}/categories/store`, form);
    };

    return (
        <MainLayout user={user} currentTenant={tenant}>
            <div className="max-w-2xl mx-auto">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold">Nueva Categoría</h1>
                    <Link
                        href={`/tenant/${tenant.code}/categories`}
                        className="text-gray-600 hover:text-gray-900"
                    >
                        ← Volver
                    </Link>
                </div>

                <form onSubmit={handleSubmit} className="bg-white shadow rounded-lg p-6">
                    <div className="mb-4">
                        <label className="block text-gray-700 text-sm font-bold mb-2">
                            Nombre
                        </label>
                        <input
                            type="text"
                            name="name"
                            value={form.name}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                            required
                        />
                    </div>

                    <div className="mb-6">
                        <label className="block text-gray-700 text-sm font-bold mb-2">
                            Descripción
                        </label>
                        <textarea
                            name="description"
                            value={form.description}
                            onChange={handleChange}
                            rows="3"
                            className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        />
                    </div>

                    <div className="flex justify-end">
                        <Link
                            href={`/tenant/${tenant.code}/categories`}
                            className="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2 hover:bg-gray-400"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                        >
                            Crear Categoría
                        </button>
                    </div>
                </form>
            </div>
        </MainLayout>
    );
}
