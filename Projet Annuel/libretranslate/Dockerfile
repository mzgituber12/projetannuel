FROM libretranslate/libretranslate:v1.9.5

RUN /app/venv/bin/python -c "import argostranslate.package as p; p.update_package_index(); targets=['ru','es','de','it','pt','nl','pl','uk']; wanted={('fr','en'),('en','fr')}; wanted|={('en',t) for t in targets}; wanted|={(t,'en') for t in targets}; [p.install_from_path(pkg.download()) for pkg in p.get_available_packages() if (pkg.from_code,pkg.to_code) in wanted]"
