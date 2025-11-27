import { useState } from "react";
import { Link, router } from "@inertiajs/react";

export default function MainLayout({ user, currentTenant, children }) {
    const [isProductsOpen, setIsProductsOpen] = useState(false);
    const [isCategoriesOpen, setIsCategoriesOpen] = useState(false);

    const handleLogout = () => {
        router.get("/logout");
    };

    const closeAllMenus = () => {
        setIsProductsOpen(false);
        setIsCategoriesOpen(false);
    };

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Navbar */}
            <nav className="bg-white shadow relative z-50">
                <div className="container mx-auto px-6 py-4">
                    <div className="flex justify-between items-center">
                        <div className="flex items-center space-x-8">
                            <Link href="/dashboard" className="text-xl font-bold text-gray-800">
                                PharmaMigue
                            </Link>

                            <div className="hidden md:flex items-center space-x-4">
                                <Link
                                    href="/dashboard"
                                    className="text-gray-600 hover:text-gray-900 px-3 py-2"
                                >
                                    Dashboard
                                </Link>

                                {user?.isAdmin && (
                                    <Link
                                        href="/users"
                                        className="text-gray-600 hover:text-gray-900 px-3 py-2"
                                    >
                                        Usuarios
                                    </Link>
                                )}

                                {/* Menú de Productos */}
                                {user?.tenants?.length > 0 && (
                                    <div className="relative">
                                        <button
                                            onClick={() => {
                                                setIsProductsOpen(!isProductsOpen);
                                                setIsCategoriesOpen(false);
                                            }}
                                            className="flex items-center text-gray-600 hover:text-gray-900 px-3 py-2"
                                        >
                                            <span>Productos</span>
                                            <svg
                                                className={`w-4 h-4 ml-1 transition-transform ${isProductsOpen ? 'rotate-180' : ''}`}
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        {isProductsOpen && (
                                            <>
                                                <div
                                                    className="fixed inset-0 z-10"
                                                    onClick={closeAllMenus}
                                                ></div>

                                                <div className="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg z-20 border">
                                                    <div className="py-1">
                                                        <div className="px-4 py-2 text-xs text-gray-500 uppercase border-b bg-gray-50">
                                                            Mis Farmacias
                                                        </div>
                                                        {user.tenants.map((tenant) => (
                                                            <Link
                                                                key={tenant.id}
                                                                href={`/tenant/${tenant.code}/products`}
                                                                onClick={closeAllMenus}
                                                                className={`block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 ${
                                                                    currentTenant?.code === tenant.code
                                                                        ? 'bg-blue-50 text-blue-700 font-medium border-l-4 border-blue-500'
                                                                        : ''
                                                                }`}
                                                            >
                                                                <div className="flex items-center">
                                                                    <span className="text-xl mr-3">📦</span>
                                                                    <div>
                                                                        <div className="font-medium">{tenant.name}</div>
                                                                        <div className="text-xs text-gray-500">{tenant.code}</div>
                                                                    </div>
                                                                </div>
                                                            </Link>
                                                        ))}
                                                    </div>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                )}

                                {/* Menú de Categorías */}
                                {user?.tenants?.length > 0 && (
                                    <div className="relative">
                                        <button
                                            onClick={() => {
                                                setIsCategoriesOpen(!isCategoriesOpen);
                                                setIsProductsOpen(false);
                                            }}
                                            className="flex items-center text-gray-600 hover:text-gray-900 px-3 py-2"
                                        >
                                            <span>Categorías</span>
                                            <svg
                                                className={`w-4 h-4 ml-1 transition-transform ${isCategoriesOpen ? 'rotate-180' : ''}`}
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        {isCategoriesOpen && (
                                            <>
                                                <div
                                                    className="fixed inset-0 z-10"
                                                    onClick={closeAllMenus}
                                                ></div>

                                                <div className="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg z-20 border">
                                                    <div className="py-1">
                                                        <div className="px-4 py-2 text-xs text-gray-500 uppercase border-b bg-gray-50">
                                                            Mis Farmacias
                                                        </div>
                                                        {user.tenants.map((tenant) => (
                                                            <Link
                                                                key={tenant.id}
                                                                href={`/tenant/${tenant.code}/categories`}
                                                                onClick={closeAllMenus}
                                                                className={`block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-700 ${
                                                                    currentTenant?.code === tenant.code
                                                                        ? 'bg-green-50 text-green-700 font-medium border-l-4 border-green-500'
                                                                        : ''
                                                                }`}
                                                            >
                                                                <div className="flex items-center">
                                                                    <span className="text-xl mr-3">🏷️</span>
                                                                    <div>
                                                                        <div className="font-medium">{tenant.name}</div>
                                                                        <div className="text-xs text-gray-500">{tenant.code}</div>
                                                                    </div>
                                                                </div>
                                                            </Link>
                                                        ))}
                                                    </div>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="flex items-center space-x-4">
                            {currentTenant && (
                                <span className="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full font-medium">
                                    📍 {currentTenant.name}
                                </span>
                            )}

                            <span className="text-gray-600">
                                {user?.name}
                            </span>
                            <button
                                onClick={handleLogout}
                                className="text-red-500 hover:text-red-700 px-3 py-2"
                            >
                                Salir
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            {currentTenant && (
                <div className="bg-blue-600 text-white py-2">
                    <div className="container mx-auto px-6">
                        <div className="flex items-center text-sm">
                            <span>Trabajando en:</span>
                            <span className="font-bold ml-2">{currentTenant.name}</span>
                            <span className="mx-2">|</span>
                            <span className="opacity-75">Base de datos: {currentTenant.code}</span>
                        </div>
                    </div>
                </div>
            )}

            <main className="container mx-auto px-6 py-8">
                {children}
            </main>
        </div>
    );
}
