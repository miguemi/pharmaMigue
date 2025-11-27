import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import MainLayout from "../Layouts/MainLayout";

export default function Edit({ user, editUser, tenants }) {
    const [form, setForm] = useState({
        name: editUser.name,
        email: editUser.email,
        password: "",
        isAdmin: editUser.isAdmin,
        isActive: editUser.isActive,
        tenantIds: editUser.tenants.map((t) => t.id),
    });

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setForm({
            ...form,
            [name]: type === "checkbox" ? checked : value,
        });
    };

    const handleTenantChange = (tenantId) => {
        setForm((prev) => {
            const tenantIds = prev.tenantIds.includes(tenantId)
                ? prev.tenantIds.filter((id) => id !== tenantId)
                : [...prev.tenantIds, tenantId];
            return { ...prev, tenantIds };
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        router.post(`/users/${editUser.id}/update`, form);
    };

    return (
        <MainLayout user={user}>
            <div className="max-w-2xl mx-auto">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold">Editar Usuario</h1>
                    <Link
                        href="/users"
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

                    <div className="mb-4">
                        <label className="block text-gray-700 text-sm font-bold mb-2">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value={form.email}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                            required
                        />
                    </div>

                    <div className="mb-4">
                        <label className="block text-gray-700 text-sm font-bold mb-2">
                            Nueva Contraseña (dejar vacío para no cambiar)
                        </label>
                        <input
                            type="password"
                            name="password"
                            value={form.password}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        />
                    </div>

                    <div className="mb-4">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="isAdmin"
                                checked={form.isAdmin}
                                onChange={handleChange}
                                className="mr-2"
                            />
                            <span className="text-gray-700 text-sm font-bold">
                                Es Administrador
                            </span>
                        </label>
                    </div>

                    <div className="mb-4">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="isActive"
                                checked={form.isActive}
                                onChange={handleChange}
                                className="mr-2"
                            />
                            <span className="text-gray-700 text-sm font-bold">
                                Usuario Activo
                            </span>
                        </label>
                    </div>

                    <div className="mb-6">
                        <label className="block text-gray-700 text-sm font-bold mb-2">
                            Tenants
                        </label>
                        <div className="space-y-2">
                            {tenants.map((tenant) => (
                                <label key={tenant.id} className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={form.tenantIds.includes(tenant.id)}
                                        onChange={() => handleTenantChange(tenant.id)}
                                        className="mr-2"
                                    />
                                    <span className="text-gray-700">
                                        {tenant.name} ({tenant.code})
                                    </span>
                                </label>
                            ))}
                        </div>
                    </div>

                    <div className="flex justify-end">
                        <Link
                            href="/users"
                            className="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2 hover:bg-gray-400"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </MainLayout>
    );
}
