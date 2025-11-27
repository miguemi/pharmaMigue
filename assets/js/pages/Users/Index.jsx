import { Link, router } from "@inertiajs/react";
import MainLayout from "../Layouts/MainLayout";

export default function Index({ user, users }) {
    const handleDelete = (id) => {
        if (confirm("¿Estás seguro de eliminar este usuario?")) {
            router.post(`/users/${id}/delete`);
        }
    };

    return (
        <MainLayout user={user}>
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Usuarios</h1>
                <Link
                    href="/users/create"
                    className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Nuevo Usuario
                </Link>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Nombre
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Email
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Rol
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tenants
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Estado
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {users.map((u) => (
                            <tr key={u.id}>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    {u.name}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    {u.email}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span
                                        className={`px-2 py-1 text-xs rounded ${
                                            u.isAdmin
                                                ? "bg-purple-100 text-purple-800"
                                                : "bg-gray-100 text-gray-800"
                                        }`}
                                    >
                                        {u.isAdmin ? "Admin" : "Usuario"}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    {u.tenants.map((t) => (
                                        <span
                                            key={t.id}
                                            className="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1"
                                        >
                                            {t.code}
                                        </span>
                                    ))}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span
                                        className={`px-2 py-1 text-xs rounded ${
                                            u.isActive
                                                ? "bg-green-100 text-green-800"
                                                : "bg-red-100 text-red-800"
                                        }`}
                                    >
                                        {u.isActive ? "Activo" : "Inactivo"}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                    <Link
                                        href={`/users/${u.id}/edit`}
                                        className="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(u.id)}
                                        className="text-red-600 hover:text-red-900"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {users.length === 0 && (
                    <div className="text-center py-8 text-gray-500">
                        No hay usuarios registrados
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
